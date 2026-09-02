<?php
/**
 * Modele Ordonnance
 * Gere aussi la table de jointure ordonnance_medicaments (N,N avec medicaments).
 */
class Ordonnance
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Toutes les ordonnances, avec jointure sur le client et le valideur.
     */
    public function all(): array
    {
        $sql = 'SELECT o.*, CONCAT(c.prenom, " ", c.nom) AS client_nom, c.email AS client_email,
                       CONCAT(v.prenom, " ", v.nom) AS valideur_nom
                FROM ordonnances o
                JOIN utilisateurs c ON c.id = o.client_id
                LEFT JOIN utilisateurs v ON v.id = o.valide_par
                ORDER BY o.date_creation DESC';
        return $this->db->query($sql)->fetchAll();
    }

    public function findById(int $id): array|false
    {
        $sql = 'SELECT o.*, CONCAT(c.prenom, " ", c.nom) AS client_nom, c.email AS client_email,
                       CONCAT(v.prenom, " ", v.nom) AS valideur_nom
                FROM ordonnances o
                JOIN utilisateurs c ON c.id = o.client_id
                LEFT JOIN utilisateurs v ON v.id = o.valide_par
                WHERE o.id = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function byClient(int $clientId): array
    {
        $stmt = $this->db->prepare(
            'SELECT o.*, CONCAT(v.prenom, " ", v.nom) AS valideur_nom
             FROM ordonnances o
             LEFT JOIN utilisateurs v ON v.id = o.valide_par
             WHERE o.client_id = :cid ORDER BY o.date_creation DESC'
        );
        $stmt->execute(['cid' => $clientId]);
        return $stmt->fetchAll();
    }

    public function byStatut(string $statut): array
    {
        $sql = 'SELECT o.*, CONCAT(c.prenom, " ", c.nom) AS client_nom
                FROM ordonnances o JOIN utilisateurs c ON c.id = o.client_id
                WHERE o.statut = :statut ORDER BY o.date_creation ASC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['statut' => $statut]);
        return $stmt->fetchAll();
    }

    /**
     * Cree une ordonnance et ses lignes de medicaments (transaction).
     * $items = [['medicament_id' => x, 'quantite' => y, 'posologie' => z], ...]
     */
    public function create(array $data, array $items): int
    {
        $this->db->beginTransaction();
        try {
            $sql = 'INSERT INTO ordonnances (client_id, medecin_nom, date_prescription, type, commentaire)
                    VALUES (:client_id, :medecin_nom, :date_prescription, :type, :commentaire)';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'client_id'         => $data['client_id'],
                'medecin_nom'       => $data['medecin_nom'],
                'date_prescription' => $data['date_prescription'],
                'type'              => $data['type'] ?? 'nouvelle',
                'commentaire'       => $data['commentaire'] ?? null,
            ]);
            $ordonnanceId = (int) $this->db->lastInsertId();

            $stmtItem = $this->db->prepare(
                'INSERT INTO ordonnance_medicaments (ordonnance_id, medicament_id, quantite, posologie)
                 VALUES (:oid, :mid, :qte, :posologie)'
            );
            foreach ($items as $item) {
                $stmtItem->execute([
                    'oid'       => $ordonnanceId,
                    'mid'       => $item['medicament_id'],
                    'qte'       => $item['quantite'],
                    'posologie' => $item['posologie'] ?? null,
                ]);
            }

            $this->db->commit();
            return $ordonnanceId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Lignes de medicaments d'une ordonnance, jointes avec le detail medicament.
     */
    public function items(int $ordonnanceId): array
    {
        $sql = 'SELECT om.*, m.nom AS medicament_nom, m.prix, m.stock, m.date_expiration, m.necessite_ordonnance
                FROM ordonnance_medicaments om
                JOIN medicaments m ON m.id = om.medicament_id
                WHERE om.ordonnance_id = :oid';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['oid' => $ordonnanceId]);
        return $stmt->fetchAll();
    }

    public function valider(int $id, int $valideurId, string $commentaire = ''): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE ordonnances SET statut = "validee", valide_par = :vid, date_validation = NOW(), commentaire = :com
             WHERE id = :id AND statut = "en_attente"'
        );
        $stmt->execute(['vid' => $valideurId, 'com' => $commentaire, 'id' => $id]);
        // rowCount() = 0 si l'ordonnance n'etait plus en attente (deja traitee)
        return $stmt->rowCount() > 0;
    }

    public function refuser(int $id, int $valideurId, string $commentaire = ''): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE ordonnances SET statut = "refusee", valide_par = :vid, date_validation = NOW(), commentaire = :com
             WHERE id = :id AND statut = "en_attente"'
        );
        $stmt->execute(['vid' => $valideurId, 'com' => $commentaire, 'id' => $id]);
        // rowCount() = 0 si l'ordonnance n'etait plus en attente (deja traitee)
        return $stmt->rowCount() > 0;
    }

    /**
     * Remet une ordonnance validee en attente.
     * Utilise comme compensation si la generation de l'expedition echoue
     * apres que le statut a ete bascule (garantit la coherence des donnees).
     */
    public function annulerValidation(int $id): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE ordonnances SET statut = "en_attente", valide_par = NULL, date_validation = NULL
             WHERE id = :id AND statut = "validee"'
        );
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM ordonnances WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function countByStatut(string $statut): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM ordonnances WHERE statut = :s');
        $stmt->execute(['s' => $statut]);
        return (int) $stmt->fetchColumn();
    }
}
