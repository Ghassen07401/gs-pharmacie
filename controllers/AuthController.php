<?php
require_once __DIR__ . '/../models/Utilisateur.php';

class AuthController extends Controller
{
    private Utilisateur $utilisateurModel;

    public function __construct()
    {
        $this->utilisateurModel = new Utilisateur();
    }

    public function login(): void
    {
        if (Auth::check()) {
            $this->redirect('index.php?c=dashboard&a=index');
        }

        $errors = [];

        if ($this->isPost()) {
            $this->requireCsrf();

            $email = $this->input('email');
            $motDePasse = $this->input('mot_de_passe');

            if (empty($email) || empty($motDePasse)) {
                $errors[] = 'Veuillez remplir tous les champs.';
            } else {
                $user = $this->utilisateurModel->findByEmail($email);
                if ($user && password_verify($motDePasse, $user['mot_de_passe'])) {
                    if ((int)$user['actif'] === 0) {
                        $errors[] = 'Votre compte est desactive. Contactez le responsable.';
                    } else {
                        Auth::login($user);
                        $this->redirect('index.php?c=dashboard&a=index');
                    }
                } else {
                    $errors[] = 'Email ou mot de passe incorrect.';
                }
            }
        }

        $this->renderPlain('auth/login', ['errors' => $errors]);
    }

    public function register(): void
    {
        if (Auth::check()) {
            $this->redirect('index.php?c=dashboard&a=index');
        }

        $errors = [];
        $old = [];

        if ($this->isPost()) {
            $this->requireCsrf();

            $old = [
                'nom'       => $this->input('nom'),
                'prenom'    => $this->input('prenom'),
                'email'     => $this->input('email'),
                'telephone' => $this->input('telephone'),
                'adresse'   => $this->input('adresse'),
            ];
            $motDePasse = $this->input('mot_de_passe');
            $confirmation = $this->input('confirmation');

            if (empty($old['nom']) || empty($old['prenom']) || empty($old['email']) || empty($motDePasse)) {
                $errors[] = 'Veuillez remplir tous les champs obligatoires.';
            }
            if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Adresse email invalide.';
            }
            if (strlen($motDePasse) < 6) {
                $errors[] = 'Le mot de passe doit contenir au moins 6 caracteres.';
            }
            if ($motDePasse !== $confirmation) {
                $errors[] = 'Les mots de passe ne correspondent pas.';
            }
            if (empty($errors) && $this->utilisateurModel->emailExists($old['email'])) {
                $errors[] = 'Cet email est deja utilise.';
            }

            // Inscription publique : uniquement pour le role client
            if (empty($errors)) {
                $this->utilisateurModel->create([
                    'nom'          => $old['nom'],
                    'prenom'       => $old['prenom'],
                    'email'        => $old['email'],
                    'mot_de_passe' => $motDePasse,
                    'role'         => 'client',
                    'telephone'    => $old['telephone'],
                    'adresse'      => $old['adresse'],
                ]);
                $this->flash('success', 'Compte cree avec succes. Vous pouvez vous connecter.');
                $this->redirect('index.php?c=auth&a=login');
            }
        }

        $this->renderPlain('auth/register', ['errors' => $errors, 'old' => $old]);
    }

    public function logout(): void
    {
        Auth::logout();
        $this->redirect('index.php?c=auth&a=login');
    }
}
