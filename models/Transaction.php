<?php
/**
 * Modele Transaction (ventes / expeditions) + transaction_details
 */
class Transaction
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function all(): array
    {
        $sql = 'SELECT t.*, CONCAT(c.prenom, " ", c.nom) AS client_nom,
                       CONCAT(p.prenom, " ", p.nom) AS pharmacien_nom
                FROM transactions t
                JOIN utilisateurs c ON c.id = t.client_id
                JOIN utilisateurs p ON p.id = t.pharmacien_id
                ORDER BY t.date_transaction DESC';
        return $this->db->query($sql)->fetchAll();
    }

    public function byClient(int $clientId): array
    {
        $sql = 'SELECT t.*, CONCAT(p.prenom, " ", p.nom) AS pharmacien_nom
                FROM transactions t JOIN utilisateurs p ON p.id = t.pharmacien_id
                WHERE t.client_id = :cid ORDER BY t.date_transaction DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['cid' => $clientId]);
        return $stmt->fetchAll();
    }

    public function byPharmacien(int $pharmacienId): array
    {
        $sql = 'SELECT t.*, CONCAT(c.prenom, " ", c.nom) AS client_nom
                FROM transactions t JOIN utilisateurs c ON c.id = t.client_id
                WHERE t.pharmacien_id = :pid ORDER BY t.date_transaction DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['pid' => $pharmacienId]);
        return $stmt->fetchAll();
    }

    public function findById(int $id): array|false
    {
        $sql = 'SELECT t.*, CONCAT(c.prenom, " ", c.nom) AS client_nom,
                       CONCAT(p.prenom, " ", p.nom) AS pharmacien_nom
                FROM transactions t
                JOIN utilisateurs c ON c.id = t.client_id
                JOIN utilisateurs p ON p.id = t.pharmacien_id
                WHERE t.id = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /** Une expedition existe-t-elle deja pour cette ordonnance ? */
    public function existsForOrdonnance(int $ordonnanceId): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM transactions WHERE ordonnance_id = :oid');
        $stmt->execute(['oid' => $ordonnanceId]);
        return (bool) $stmt->fetchColumn();
    }

    public function details(int $transactionId): array
    {
        $sql = 'SELECT td.*, m.nom AS medicament_nom
                FROM transaction_details td
                JOIN medicaments m ON m.id = td.medicament_id
                WHERE td.transaction_id = :tid';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tid' => $transactionId]);
        return $stmt->fetchAll();
    }

    /**
     * Cree une transaction (expedition) a partir d'une ordonnance validee et
     * decremente le stock des medicaments concernes.
     *
     * Le decrement est conditionne par "AND stock >= :qte" : si le stock est
     * insuffisant, aucune ligne n'est affectee, une exception est levee et
     * l'ensemble de l'operation est annule (rollBack). Le stock ne peut donc
     * jamais devenir negatif, meme en cas de validations simultanees.
     *
     * @throws RuntimeException si le stock est insuffisant pour un medicament
     */
    public function createFromOrdonnance(int $ordonnanceId, int $clientId, int $pharmacienId, array $items): int
    {
        $this->db->beginTransaction();
        try {
            $montantTotal = 0;
            foreach ($items as $item) {
                $montantTotal += $item['prix'] * $item['quantite'];
            }

            $stmt = $this->db->prepare(
                'INSERT INTO transactions (ordonnance_id, client_id, pharmacien_id, montant_total)
                 VALUES (:oid, :cid, :pid, :montant)'
            );
            $stmt->execute([
                'oid'     => $ordonnanceId,
                'cid'     => $clientId,
                'pid'     => $pharmacienId,
                'montant' => $montantTotal,
            ]);
            $transactionId = (int) $this->db->lastInsertId();

            $stmtDetail = $this->db->prepare(
                'INSERT INTO transaction_details (transaction_id, medicament_id, quantite, prix_unitaire)
                 VALUES (:tid, :mid, :qte, :prix)'
            );
            $stmtStock = $this->db->prepare(
                // Deux marqueurs distincts : avec ATTR_EMULATE_PREPARES => false,
                // PDO n autorise pas la reutilisation d un meme parametre nomme.
                'UPDATE medicaments SET stock = stock - :qte WHERE id = :mid AND stock >= :qte_min'
            );

            foreach ($items as $item) {
                $stmtDetail->execute([
                    'tid'  => $transactionId,
                    'mid'  => $item['medicament_id'],
                    'qte'  => $item['quantite'],
                    'prix' => $item['prix'],
                ]);
                $stmtStock->execute([
                    'qte'     => $item['quantite'],
                    'qte_min' => $item['quantite'],
                    'mid'     => $item['medicament_id'],
                ]);

                if ($stmtStock->rowCount() === 0) {
                    throw new RuntimeException(
                        'Stock insuffisant pour le medicament #' . (int) $item['medicament_id'] . '.'
                    );
                }
            }

            $this->db->commit();
            return $transactionId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /** Rapport : expeditions/ventes recentes (30 derniers jours par defaut) */
    public function recentes(int $jours = 30): array
    {
        $sql = 'SELECT t.*, CONCAT(c.prenom, " ", c.nom) AS client_nom,
                       CONCAT(p.prenom, " ", p.nom) AS pharmacien_nom
                FROM transactions t
                JOIN utilisateurs c ON c.id = t.client_id
                JOIN utilisateurs p ON p.id = t.pharmacien_id
                WHERE t.date_transaction >= DATE_SUB(NOW(), INTERVAL :jours DAY)
                ORDER BY t.date_transaction DESC';
        $stmt = $this->db->prepare($sql);
        // Type entier explicite : indispensable avec ATTR_EMULATE_PREPARES => false
        $stmt->bindValue(':jours', max(1, $jours), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
