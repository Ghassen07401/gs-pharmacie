<?php
/**
 * Classe Auth
 * Gestion centralisee de l'authentification et des droits d'acces par role.
 */
class Auth
{
    public static function login(array $utilisateur): void
    {
        // Regeneration de l'identifiant de session : protection contre la
        // fixation de session (un identifiant connu avant login devient invalide).
        session_regenerate_id(true);

        $_SESSION['user_id']    = $utilisateur['id'];
        $_SESSION['user_nom']   = $utilisateur['nom'];
        $_SESSION['user_prenom']= $utilisateur['prenom'];
        $_SESSION['user_email'] = $utilisateur['email'];
        $_SESSION['user_role']  = $utilisateur['role'];
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    public static function check(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public static function id(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    public static function role(): ?string
    {
        return $_SESSION['user_role'] ?? null;
    }

    public static function fullName(): string
    {
        return ($_SESSION['user_prenom'] ?? '') . ' ' . ($_SESSION['user_nom'] ?? '');
    }

    /**
     * Redirige vers le login si non connecte.
     */
    public static function requireLogin(): void
    {
        if (!self::check()) {
            header('Location: index.php?c=auth&a=login');
            exit;
        }
    }

    /**
     * Redirige (ou affiche une erreur 403) si le role de l'utilisateur
     * ne fait pas partie des roles autorises.
     * @param string[] $roles
     */
    public static function requireRole(array $roles): void
    {
        self::requireLogin();
        if (!in_array(self::role(), $roles, true)) {
            http_response_code(403);
            include __DIR__ . '/../views/errors/403.php';
            exit;
        }
    }
}
