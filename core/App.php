<?php
/**
 * Classe App
 * Routeur minimaliste "fait maison" (pas de framework).
 * URL attendues : index.php?c=controleur&a=action&...params
 */
class App
{
    private string $controllerName;
    private string $action;
    private array $params;

    public function __construct()
    {
        $this->controllerName = $_GET['c'] ?? 'auth';
        $this->action         = $_GET['a'] ?? 'login';
        $this->params         = $_GET;
    }

    public function run(): void
    {
        // Le nom de controleur vient de l'URL : on n'accepte que des lettres,
        // ce qui interdit toute tentative de traversee de repertoire (../).
        if (!preg_match('/^[a-zA-Z]+$/', $this->controllerName)
            || !preg_match('/^[a-zA-Z]+$/', $this->action)) {
            $this->notFound();
            return;
        }

        $className = ucfirst(strtolower($this->controllerName)) . 'Controller';
        $classFile = __DIR__ . '/../controllers/' . $className . '.php';

        if (!file_exists($classFile)) {
            $this->notFound();
            return;
        }

        require_once $classFile;

        if (!class_exists($className)) {
            $this->notFound();
            return;
        }

        $controller = new $className();

        // On ne route que vers les methodes PUBLIQUES et non magiques du controleur.
        // method_exists() ne suffit pas : il renvoie true pour les methodes privees
        // et protegees (render, redirect, validatedInput...), dont l'appel provoquerait
        // une erreur fatale exposant l'arborescence du serveur.
        $reflection = new ReflectionClass($controller);

        if (!$reflection->hasMethod($this->action)) {
            $this->notFound();
            return;
        }

        $method = $reflection->getMethod($this->action);

        if (!$method->isPublic() || $method->isStatic() || str_starts_with($this->action, '__')) {
            $this->notFound();
            return;
        }

        $controller->{$this->action}($this->params);
    }

    private function notFound(): void
    {
        http_response_code(404);
        require __DIR__ . '/../views/errors/404.php';
    }
}
