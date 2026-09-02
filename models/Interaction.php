<?php
/**
 * Modele Interaction (interactions_medicamenteuses)
 */
class Interaction
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function all(): array
    {
        $sql = 'SELECT i.*, m1.nom AS med1_nom, m2.nom AS med2_nom, CONCAT(u.prenom, " ", u.nom) AS enregistre_par_nom
                FROM interactions_medicamenteuses i
                JOIN medicaments m1 ON m1.id = i.medicament_1_id
                JOIN medicaments m2 ON m2.id = i.medicament_2_id
                LEFT JOIN utilisateurs u ON u.id = i.enregistre_par
                ORDER BY i.date_enregistrement DESC';
        return $this->db->query($sql)->fetchAll();
    }

    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM interactions_medicamenteuses WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function create(array $data): int
    {
        $sql = 'INSERT INTO interactions_medicamenteuses (medicament_1_id, medicament_2_id, description, niveau_gravite, enregistre_par)
                VALUES (:m1, :m2, :description, :gravite, :par)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'm1'          => $data['medicament_1_id'],
            'm2'          => $data['medicament_2_id'],
            'description' => $data['description'],
            'gravite'     => $data['niveau_gravite'],
            'par'         => $data['enregistre_par'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM interactions_medicamenteuses WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Verifie les interactions potentielles pour un ensemble donne d'ids de medicaments
     * (utilise quand un client soumet une ordonnance avec plusieurs medicaments).
     */
    public function checkForMedicaments(array $medicamentIds): array
    {
        $medicamentIds = array_values(array_unique(array_map('intval', $medicamentIds)));
        if (count($medicamentIds) < 2) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($medicamentIds), '?'));
        $sql = "SELECT i.*, m1.nom AS med1_nom, m2.nom AS med2_nom
                FROM interactions_medicamenteuses i
                JOIN medicaments m1 ON m1.id = i.medicament_1_id
                JOIN medicaments m2 ON m2.id = i.medicament_2_id
                WHERE i.medicament_1_id IN ($placeholders)
                  AND i.medicament_2_id IN ($placeholders)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_merge($medicamentIds, $medicamentIds));
        return $stmt->fetchAll();
    }
}
