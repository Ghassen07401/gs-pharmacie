<?php
/**
 * Classe Csrf
 * Protection contre les attaques CSRF (Cross-Site Request Forgery).
 *
 * Principe : un jeton aleatoire est stocke en session et insere dans chaque
 * formulaire. A la soumission, le jeton recu est compare a celui de la session.
 * Un site tiers ne peut pas connaitre ce jeton : il ne peut donc pas forger
 * une requete valide au nom de l'utilisateur connecte.
 */
class Csrf
{
    private const SESSION_KEY = 'csrf_token';

    /** Jeton de la session courante (genere au premier appel). */
    public static function token(): string
    {
        if (empty($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::SESSION_KEY];
    }

    /** Champ cache a inserer dans chaque formulaire POST. */
    public static function field(): string
    {
        return '<input type="hidden" name="csrf_token" value="'
             . htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Verifie le jeton recu en POST.
     * hash_equals() compare en temps constant (pas de fuite par timing).
     */
    public static function isValid(): bool
    {
        $recu = $_POST['csrf_token'] ?? '';
        return is_string($recu)
            && $recu !== ''
            && !empty($_SESSION[self::SESSION_KEY])
            && hash_equals($_SESSION[self::SESSION_KEY], $recu);
    }
}
