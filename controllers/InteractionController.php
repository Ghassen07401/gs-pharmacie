<?php
require_once __DIR__ . '/../models/Interaction.php';
require_once __DIR__ . '/../models/Medicament.php';

class InteractionController extends Controller
{
    private Interaction $model;
    private Medicament $medicamentModel;

    public function __construct()
    {
        $this->model = new Interaction();
        $this->medicamentModel = new Medicament();
    }

    public function index(): void
    {
        Auth::requireRole(['pharmacien', 'responsable']);
        $this->render('pharmacien/interactions_liste', ['interactions' => $this->model->all()]);
    }

    public function ajouter(): void
    {
        Auth::requireRole(['pharmacien', 'responsable']);
        $errors = [];

        if ($this->isPost()) {
            $this->requireCsrf();

            $med1 = (int) $this->input('medicament_1_id');
            $med2 = (int) $this->input('medicament_2_id');
            $description = $this->input('description');
            $gravite = $this->input('niveau_gravite');

            if ($med1 === $med2) {
                $errors[] = 'Veuillez selectionner deux medicaments differents.';
            }
            if (empty($description) || strlen($description) < 10) {
                $errors[] = 'La description doit contenir au moins 10 caracteres.';
            }
            if (!in_array($gravite, ['faible', 'moderee', 'grave'], true)) {
                $errors[] = 'Niveau de gravite invalide.';
            }

            if (empty($errors)) {
                $this->model->create([
                    'medicament_1_id' => $med1,
                    'medicament_2_id' => $med2,
                    'description'     => $description,
                    'niveau_gravite'  => $gravite,
                    'enregistre_par'  => Auth::id(),
                ]);
                $this->flash('success', 'Interaction medicamenteuse enregistree.');
                $this->redirect('index.php?c=interaction&a=index');
            }
        }

        $this->render('pharmacien/interaction_form', [
            'errors'      => $errors,
            'medicaments' => $this->medicamentModel->all(),
        ]);
    }

    public function supprimer(array $params): void
    {
        Auth::requireRole(['pharmacien', 'responsable']);
        $this->requirePostCsrf();
        $id = (int) ($params['id'] ?? 0);
        $this->model->delete($id);
        $this->flash('success', 'Interaction supprimee.');
        $this->redirect('index.php?c=interaction&a=index');
    }
}
