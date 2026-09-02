<?php
/**
 * Modele Medicament
 */
class Medicament
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function all(): array
    {
        $stmt = $this->db->query('SELECT * FROM medicaments ORDER BY nom');
        return $stmt->fetchAll();
    }

    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM medicaments WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function create(array $data): int
    {
        $sql = 'INSERT INTO medicaments (nom, description, categorie, fabricant, prix, stock, stock_minimum, date_expiration, necessite_ordonnance)
                VALUES (:nom, :description, :categorie, :fabricant, :prix, :stock, :stock_minimum, :date_expiration, :necessite_ordonnance)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'nom'                  => $data['nom'],
            'description'          => $data['description'] ?? null,
            'categorie'            => $data['categorie'],
            'fabricant'            => $data['fabricant'] ?? null,
            'prix'                 => $data['prix'],
            'stock'                => $data['stock'],
            'stock_minimum'        => $data['stock_minimum'],
            'date_expiration'      => $data['date_expiration'] ?: null,
            'necessite_ordonnance' => $data['necessite_ordonnance'] ?? 0,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $sql = 'UPDATE medicaments SET nom = :nom, description = :description, categorie = :categorie,
                fabricant = :fabricant, prix = :prix, stock = :stock, stock_minimum = :stock_minimum,
                date_expiration = :date_expiration, necessite_ordonnance = :necessite_ordonnance
                WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'nom'                  => $data['nom'],
            'description'          => $data['description'] ?? null,
            'categorie'            => $data['categorie'],
            'fabricant'            => $data['fabricant'] ?? null,
            'prix'                 => $data['prix'],
            'stock'                => $data['stock'],
            'stock_minimum'        => $data['stock_minimum'],
            'date_expiration'      => $data['date_expiration'] ?: null,
            'necessite_ordonnance' => $data['necessite_ordonnance'] ?? 0,
            'id'                   => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM medicaments WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function decrementStock(int $id, int $quantite): bool
    {
        // Deux marqueurs distincts : PDO n autorise pas la reutilisation
        // d un meme parametre nomme quand l emulation est desactivee.
        $stmt = $this->db->prepare('UPDATE medicaments SET stock = stock - :q WHERE id = :id AND stock >= :q_min');
        $stmt->execute(['q' => $quantite, 'q_min' => $quantite, 'id' => $id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Recherche multicritere : nom, categorie, fabricant, plage de prix,
     * necessite d'ordonnance, stock critique uniquement.
     */
    public function search(array $criteres): array
    {
        $sql = 'SELECT * FROM medicaments WHERE 1=1';
        $params = [];

        if (!empty($criteres['nom'])) {
            $sql .= ' AND nom LIKE :nom';
            $params['nom'] = '%' . $criteres['nom'] . '%';
        }
        if (!empty($criteres['categorie'])) {
            $sql .= ' AND categorie = :categorie';
            $params['categorie'] = $criteres['categorie'];
        }
        if (!empty($criteres['fabricant'])) {
            $sql .= ' AND fabricant LIKE :fabricant';
            $params['fabricant'] = '%' . $criteres['fabricant'] . '%';
        }
        if (isset($criteres['prix_min']) && $criteres['prix_min'] !== '') {
            $sql .= ' AND prix >= :prix_min';
            $params['prix_min'] = $criteres['prix_min'];
        }
        if (isset($criteres['prix_max']) && $criteres['prix_max'] !== '') {
            $sql .= ' AND prix <= :prix_max';
            $params['prix_max'] = $criteres['prix_max'];
        }
        if (isset($criteres['necessite_ordonnance']) && $criteres['necessite_ordonnance'] !== '') {
            $sql .= ' AND necessite_ordonnance = :no';
            $params['no'] = $criteres['necessite_ordonnance'];
        }
        if (!empty($criteres['disponible'])) {
            // Disponible = en stock ET non perime : un produit perime
            // reste au catalogue interne mais ne doit plus etre proposable.
            $sql .= ' AND stock > 0';
            $sql .= ' AND (date_expiration IS NULL OR date_expiration >= CURDATE())';
        }
        if (!empty($criteres['stock_critique'])) {
            $sql .= ' AND stock <= stock_minimum';
        }

        $sql .= ' ORDER BY nom';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Rapport : medicaments dont le stock est au niveau ou en dessous du seuil minimum. */
    public function stockCritique(): array
    {
        $stmt = $this->db->query('SELECT * FROM medicaments WHERE stock <= stock_minimum ORDER BY stock ASC');
        return $stmt->fetchAll();
    }

    /**
     * Rapport : medicaments dont la date de peremption est depassee.
     * Ces produits sont physiquement en stock mais ne doivent plus etre delivres.
     */
    public function perimes(): array
    {
        $stmt = $this->db->query(
            'SELECT *, DATEDIFF(CURDATE(), date_expiration) AS jours_depuis_expiration
             FROM medicaments
             WHERE date_expiration IS NOT NULL
               AND date_expiration < CURDATE()
             ORDER BY date_expiration'
        );
        return $stmt->fetchAll();
    }

    /**
     * Rapport : medicaments qui expirent dans les $jours prochains jours.
     * Permet au responsable d anticiper un retrait ou une promotion.
     */
    public function bientotPerimes(int $jours = 90): array
    {
        $stmt = $this->db->prepare(
            'SELECT *, DATEDIFF(date_expiration, CURDATE()) AS jours_restants
             FROM medicaments
             WHERE date_expiration IS NOT NULL
               AND date_expiration >= CURDATE()
               AND date_expiration <= DATE_ADD(CURDATE(), INTERVAL :jours DAY)
             ORDER BY date_expiration'
        );
        $stmt->bindValue(':jours', max(1, $jours), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Nombre de medicaments perimes (compteur du tableau de bord). */
    public function countPerimes(): int
    {
        return (int) $this->db->query(
            'SELECT COUNT(*) FROM medicaments
             WHERE date_expiration IS NOT NULL AND date_expiration < CURDATE()'
        )->fetchColumn();
    }

    /**
     * Etat de peremption d une date, pour l affichage et les controles.
     * Retourne 'perime', 'bientot' ou '' (aucune alerte).
     *
     * Methode statique : la regle est unique et partagee par les vues,
     * les controleurs et les rapports, sans dupliquer le calcul de dates.
     */
    public static function etatPeremption(?string $dateExpiration, int $seuilJours = 90): string
    {
        if (empty($dateExpiration)) {
            return '';
        }

        $expiration = DateTime::createFromFormat('Y-m-d', $dateExpiration);
        if (!$expiration) {
            return '';
        }
        $expiration->setTime(0, 0);
        $aujourdhui = new DateTime('today');

        if ($expiration < $aujourdhui) {
            return 'perime';
        }

        return (int) $aujourdhui->diff($expiration)->days <= $seuilJours ? 'bientot' : '';
    }

    /**
     * Nombre de jours avant peremption (negatif si la date est depassee).
     * Retourne null si aucune date n est renseignee.
     */
    public static function joursAvantPeremption(?string $dateExpiration): ?int
    {
        if (empty($dateExpiration)) {
            return null;
        }
        $expiration = DateTime::createFromFormat('Y-m-d', $dateExpiration);
        if (!$expiration) {
            return null;
        }
        $expiration->setTime(0, 0);
        $intervalle = (new DateTime('today'))->diff($expiration);

        return (int) $intervalle->days * ($intervalle->invert ? -1 : 1);
    }

    public function categories(): array
    {
        $stmt = $this->db->query('SELECT DISTINCT categorie FROM medicaments ORDER BY categorie');
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function disponiblesPourClient(): array
    {
        $stmt = $this->db->query(
            'SELECT * FROM medicaments
             WHERE stock > 0
               AND (date_expiration IS NULL OR date_expiration >= CURDATE())
             ORDER BY nom'
        );
        return $stmt->fetchAll();
    }
}
