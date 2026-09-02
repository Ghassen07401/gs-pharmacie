<?php
require_once __DIR__ . '/../models/Utilisateur.php';

class UtilisateurController extends Controller
{
    private Utilisateur $model;

    public function __construct()
    {
        $this->model = new Utilisateur();
    }

    public function index(): void
    {
        Auth::requireRole(['responsable']);
        $terme = $this->query('terme', '');
        $role = $this->query('role', '');
        $utilisateurs = ($terme !== '' || $role !== '')
            ? $this->model->search($terme, $role)
            : $this->model->all();

        $this->render('responsable/utilisateurs_liste', ['utilisateurs' => $utilisateurs, 'terme' => $terme, 'role' => $role]);
    }

    public function ajouter(): void
    {
        Auth::requireRole(['responsable']);
        $errors = [];
        $old = [];

        if ($this->isPost()) {
            $this->requireCsrf();

            $old = $this->collectInput();
            $errors = $this->validate($old, true);

            if (empty($errors)) {
                $this->model->create($old);
                $this->flash('success', 'Utilisateur cree avec succes.');
                $this->redirect('index.php?c=utilisateur&a=index');
            }
        }

        $this->render('responsable/utilisateur_form', ['errors' => $errors, 'utilisateur' => $old]);
    }

    public function modifier(array $params): void
    {
        Auth::requireRole(['responsable']);
        $id = (int) ($params['id'] ?? 0);
        $utilisateur = $this->model->findById($id);

        if (!$utilisateur) {
            $this->flash('error', 'Utilisateur introuvable.');
            $this->redirect('index.php?c=utilisateur&a=index');
        }

        $errors = [];

        if ($this->isPost()) {
            $this->requireCsrf();

            $data = $this->collectInput();
            $data['actif'] = $this->input('actif', '1') === '1' ? 1 : 0;
            $errors = $this->validate($data, false, $id);

            // Le mot de passe est valide AVANT toute ecriture : sinon les autres
            // champs seraient deja enregistres alors qu une erreur est affichee.
            $nouveauMdp = $this->input('mot_de_passe');
            if (!empty($nouveauMdp) && strlen($nouveauMdp) < 6) {
                $errors[] = 'Le nouveau mot de passe doit contenir au moins 6 caracteres.';
            }

            if (empty($errors)) {
                $this->model->update($id, $data);

                if (!empty($nouveauMdp)) {
                    $this->model->updatePassword($id, $nouveauMdp);
                }

                $this->flash('success', 'Utilisateur mis a jour.');
                $this->redirect('index.php?c=utilisateur&a=index');
            }
            $utilisateur = array_merge($utilisateur, $data);
        }

        $this->render('responsable/utilisateur_form', ['errors' => $errors, 'utilisateur' => $utilisateur]);
    }

    public function supprimer(array $params): void
    {
        Auth::requireRole(['responsable']);
        $this->requirePostCsrf();
        $id = (int) ($params['id'] ?? 0);

        if ($id === Auth::id()) {
            $this->flash('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        } else {
            $this->model->delete($id);
            $this->flash('success', 'Utilisateur supprime.');
        }
        $this->redirect('index.php?c=utilisateur&a=index');
    }

    private function collectInput(): array
    {
        return [
            'nom'          => $this->input('nom'),
            'prenom'       => $this->input('prenom'),
            'email'        => $this->input('email'),
            'mot_de_passe' => $this->input('mot_de_passe'),
            'role'         => $this->input('role'),
            'telephone'    => $this->input('telephone'),
            'adresse'      => $this->input('adresse'),
        ];
    }

    private function validate(array $data, bool $requirePassword, ?int $excludeId = null): array
    {
        $errors = [];
        if (empty($data['nom']) || empty($data['prenom'])) {
            $errors[] = 'Le nom et le prenom sont obligatoires.';
        }
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Adresse email invalide.';
        } elseif ($this->model->emailExists($data['email'], $excludeId)) {
            $errors[] = 'Cet email est deja utilise par un autre compte.';
        }
        if (!in_array($data['role'], ['responsable', 'pharmacien', 'client'], true)) {
            $errors[] = 'Role invalide.';
        }
        if ($requirePassword && (empty($data['mot_de_passe']) || strlen($data['mot_de_passe']) < 6)) {
            $errors[] = 'Le mot de passe doit contenir au moins 6 caracteres.';
        }
        return $errors;
    }
}
