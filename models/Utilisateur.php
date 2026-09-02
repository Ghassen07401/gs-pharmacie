<?php
/**
 * Modele Utilisateur
 * Represente les 3 roles : responsable, pharmacien, client.
 */
class Utilisateur
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findByEmail(string $email): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM utilisateurs WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }

    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM utilisateurs WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        if ($excludeId) {
            $stmt = $this->db->prepare('SELECT COUNT(*) FROM utilisateurs WHERE email = :email AND id != :id');
            $stmt->execute(['email' => $email, 'id' => $excludeId]);
        } else {
            $stmt = $this->db->prepare('SELECT COUNT(*) FROM utilisateurs WHERE email = :email');
            $stmt->execute(['email' => $email]);
        }
        return (bool) $stmt->fetchColumn();
    }

    public function create(array $data): int
    {
        $sql = 'INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role, telephone, adresse)
                VALUES (:nom, :prenom, :email, :mot_de_passe, :role, :telephone, :adresse)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'nom'          => $data['nom'],
            'prenom'       => $data['prenom'],
            'email'        => $data['email'],
            'mot_de_passe' => password_hash($data['mot_de_passe'], PASSWORD_DEFAULT),
            'role'         => $data['role'],
            'telephone'    => $data['telephone'] ?? null,
            'adresse'      => $data['adresse'] ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $sql = 'UPDATE utilisateurs SET nom = :nom, prenom = :prenom, email = :email,
                role = :role, telephone = :telephone, adresse = :adresse, actif = :actif
                WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'nom'       => $data['nom'],
            'prenom'    => $data['prenom'],
            'email'     => $data['email'],
            'role'      => $data['role'],
            'telephone' => $data['telephone'] ?? null,
            'adresse'   => $data['adresse'] ?? null,
            'actif'     => $data['actif'] ?? 1,
            'id'        => $id,
        ]);
    }

    public function updatePassword(int $id, string $newPassword): bool
    {
        $stmt = $this->db->prepare('UPDATE utilisateurs SET mot_de_passe = :mdp WHERE id = :id');
        return $stmt->execute([
            'mdp' => password_hash($newPassword, PASSWORD_DEFAULT),
            'id'  => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM utilisateurs WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function all(): array
    {
        $stmt = $this->db->query('SELECT * FROM utilisateurs ORDER BY date_creation DESC');
        return $stmt->fetchAll();
    }

    public function allByRole(string $role): array
    {
        $stmt = $this->db->prepare('SELECT * FROM utilisateurs WHERE role = :role ORDER BY nom');
        $stmt->execute(['role' => $role]);
        return $stmt->fetchAll();
    }

    /**
     * Recherche multicritere simple (utilisee aussi pour la gestion des comptes).
     */
    public function search(string $terme, string $role = ''): array
    {
        $sql = 'SELECT * FROM utilisateurs WHERE (nom LIKE :terme OR prenom LIKE :terme OR email LIKE :terme)';
        $params = ['terme' => "%$terme%"];
        if ($role !== '') {
            $sql .= ' AND role = :role';
            $params['role'] = $role;
        }
        $sql .= ' ORDER BY nom';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
