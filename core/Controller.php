<?php
/**
 * Classe Controller
 * Classe de base dont tous les controleurs heritent.
 */
abstract class Controller
{
    /**
     * Charge une vue en lui injectant des donnees, a l'interieur d'un layout commun.
     */
    protected function render(string $view, array $data = [], string $layout = 'layouts/main'): void
    {
        extract($data);
        $viewFile = __DIR__ . '/../views/' . $view . '.php';

        if (!file_exists($viewFile)) {
            die('Vue introuvable : ' . htmlspecialchars($view));
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        $layoutFile = __DIR__ . '/../views/' . $layout . '.php';
        require $layoutFile;
    }

    /**
     * Rend une vue sans layout (utile pour les pages d'authentification).
     */
    protected function renderPlain(string $view, array $data = []): void
    {
        extract($data);
        $viewFile = __DIR__ . '/../views/' . $view . '.php';
        require $viewFile;
    }

    protected function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    protected function input(string $key, $default = null)
    {
        return isset($_POST[$key]) ? trim($_POST[$key]) : $default;
    }

    protected function query(string $key, $default = null)
    {
        return isset($_GET[$key]) ? trim($_GET[$key]) : $default;
    }

    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * Rejette la requete si le jeton CSRF est absent ou invalide.
     * A appeler au debut de chaque traitement POST.
     */
    protected function requireCsrf(): void
    {
        if (!Csrf::isValid()) {
            http_response_code(403);
            include __DIR__ . '/../views/errors/403.php';
            exit;
        }
    }

    /**
     * Impose une requete POST accompagnee d un jeton CSRF valide.
     * Utilise par les actions destructrices (suppression) : une simple URL
     * en GET ne doit jamais pouvoir modifier ou supprimer des donnees.
     */
    protected function requirePostCsrf(): void
    {
        if (!$this->isPost()) {
            http_response_code(405);
            include __DIR__ . '/../views/errors/403.php';
            exit;
        }
        $this->requireCsrf();
    }

    /**
     * Envoie un fichier CSV en telechargement.
     * Aucune bibliotheque externe : fputcsv fait partie de PHP.
     *
     * Separateur ";" et BOM UTF-8 en tete de flux, sans quoi Excel en
     * configuration francaise place toute la ligne dans une seule colonne
     * et affiche les accents de travers.
     *
     * @param string[]   $entetes ligne d'en-tete
     * @param array[]    $lignes  tableau de tableaux de valeurs scalaires
     */
    protected function exportCsv(string $nomFichier, array $entetes, array $lignes): void
    {
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $nomFichier . '"');
        header('Cache-Control: no-store, no-cache, must-revalidate');

        $sortie = fopen('php://output', 'w');
        fwrite($sortie, "\xEF\xBB\xBF");

        fputcsv($sortie, $entetes, ';', '"', '');
        foreach ($lignes as $ligne) {
            fputcsv($sortie, $ligne, ';', '"', '');
        }

        fclose($sortie);
        exit;
    }

    protected function flash(string $type, string $message): void
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }
}
