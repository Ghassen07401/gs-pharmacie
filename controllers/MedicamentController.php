<?php
require_once __DIR__ . '/../models/Medicament.php';

class MedicamentController extends Controller
{
    private Medicament $model;

    public function __construct()
    {
        $this->model = new Medicament();
    }

    /** Liste + recherche multicritere (Responsable) */
    public function index(): void
    {
        Auth::requireRole(['responsable']);

        $criteres = [
            'nom'                  => $this->query('nom', ''),
            'categorie'            => $this->query('categorie', ''),
            'fabricant'            => $this->query('fabricant', ''),
            'prix_min'             => $this->query('prix_min', ''),
            'prix_max'             => $this->query('prix_max', ''),
            'necessite_ordonnance' => $this->query('necessite_ordonnance', ''),
            'stock_critique'       => $this->query('stock_critique', ''),
        ];

        $hasCriteria = array_filter($criteres, fn($v) => $v !== '');
        $medicaments = $hasCriteria ? $this->model->search($criteres) : $this->model->all();

        $this->render('responsable/medicaments_liste', [
            'medicaments' => $medicaments,
            'categories'  => $this->model->categories(),
            'criteres'    => $criteres,
        ]);
    }

    public function ajouter(): void
    {
        Auth::requireRole(['responsable']);
        $errors = [];

        if ($this->isPost()) {
            $this->requireCsrf();

            $data = $this->validatedInput($errors);
            if (empty($errors)) {
                $this->model->create($data);
                $this->flash('success', 'Medicament ajoute avec succes.');
                $this->redirect('index.php?c=medicament&a=index');
            }
        }

        $this->render('responsable/medicament_form', [
            'errors'     => $errors,
            'medicament' => null,
            'categories' => $this->model->categories(),
        ]);
    }

    public function modifier(array $params): void
    {
        Auth::requireRole(['responsable']);
        $id = (int) ($params['id'] ?? 0);
        $medicament = $this->model->findById($id);

        if (!$medicament) {
            $this->flash('error', 'Medicament introuvable.');
            $this->redirect('index.php?c=medicament&a=index');
        }

        $errors = [];

        if ($this->isPost()) {
            $this->requireCsrf();

            $data = $this->validatedInput($errors);
            if (empty($errors)) {
                $this->model->update($id, $data);
                $this->flash('success', 'Medicament mis a jour avec succes.');
                $this->redirect('index.php?c=medicament&a=index');
            }
            $medicament = array_merge($medicament, $data);
        }

        $this->render('responsable/medicament_form', [
            'errors'     => $errors,
            'medicament' => $medicament,
            'categories' => $this->model->categories(),
        ]);
    }

    public function supprimer(array $params): void
    {
        Auth::requireRole(['responsable']);
        $this->requirePostCsrf();
        $id = (int) ($params['id'] ?? 0);
        $this->model->delete($id);
        $this->flash('success', 'Medicament supprime.');
        $this->redirect('index.php?c=medicament&a=index');
    }

    /** Catalogue consultable par le client (lecture seule) */
    public function catalogue(): void
    {
        Auth::requireRole(['client', 'pharmacien']);
        // Le catalogue ne presente que les medicaments effectivement disponibles
        $terme = $this->query('nom', '');
        $medicaments = $this->model->search(['nom' => $terme, 'disponible' => 1]);

        $this->render('client/catalogue', ['medicaments' => $medicaments, 'terme' => $terme]);
    }

    /** Rapport des stocks critiques (Responsable) */
    public function stockCritique(): void
    {
        Auth::requireRole(['responsable']);
        $this->render('responsable/rapport_stock', ['medicaments' => $this->model->stockCritique()]);
    }

    /**
     * Rapport des peremptions (Responsable).
     * Deux listes distinctes : ce qui est deja perime et doit etre retire
     * immediatement, et ce qui expire bientot et peut encore etre ecoule.
     */
    public function peremptions(): void
    {
        Auth::requireRole(['responsable']);

        $jours = (int) $this->query('jours', 90);
        $jours = $jours > 0 ? $jours : 90;

        $this->render('responsable/rapport_peremption', [
            'perimes' => $this->model->perimes(),
            'bientot' => $this->model->bientotPerimes($jours),
            'jours'   => $jours,
        ]);
    }

    /** Export CSV du rapport des peremptions (Responsable) */
    public function exportPeremptionsCsv(): void
    {
        Auth::requireRole(['responsable']);

        $jours = (int) $this->query('jours', 90);
        $jours = $jours > 0 ? $jours : 90;

        $lignes = [];
        foreach ($this->model->perimes() as $m) {
            $lignes[] = [
                'PERIME', $m['nom'], $m['categorie'], $m['fabricant'],
                (int) $m['stock'], $m['date_expiration'],
                '-' . (int) $m['jours_depuis_expiration'],
            ];
        }
        foreach ($this->model->bientotPerimes($jours) as $m) {
            $lignes[] = [
                'A SURVEILLER', $m['nom'], $m['categorie'], $m['fabricant'],
                (int) $m['stock'], $m['date_expiration'],
                (int) $m['jours_restants'],
            ];
        }

        $this->exportCsv(
            'peremptions_' . date('Y-m-d') . '.csv',
            ['Etat', 'Nom', 'Categorie', 'Fabricant', 'Stock', 'Date expiration', 'Jours restants'],
            $lignes
        );
    }

    /** Export CSV du rapport de stock critique (Responsable) */
    public function exportStockCsv(): void
    {
        Auth::requireRole(['responsable']);

        $lignes = array_map(fn($m) => [
            $m['nom'],
            $m['categorie'],
            $m['fabricant'],
            (int) $m['stock'],
            (int) $m['stock_minimum'],
            (int) $m['stock'] - (int) $m['stock_minimum'],
            number_format((float) $m['prix'], 2, ',', ''),
            $m['date_expiration'],
        ], $this->model->stockCritique());

        $this->exportCsv(
            'stock_critique_' . date('Y-m-d') . '.csv',
            ['Nom', 'Categorie', 'Fabricant', 'Stock actuel', 'Seuil minimum', 'Ecart', 'Prix unitaire', 'Date expiration'],
            $lignes
        );
    }

    private function validatedInput(array &$errors): array
    {
        $data = [
            'nom'                  => $this->input('nom'),
            'description'          => $this->input('description'),
            'categorie'            => $this->input('categorie'),
            'fabricant'            => $this->input('fabricant'),
            'prix'                 => $this->input('prix'),
            'stock'                => $this->input('stock'),
            'stock_minimum'        => $this->input('stock_minimum'),
            'date_expiration'      => $this->input('date_expiration'),
            'necessite_ordonnance' => $this->input('necessite_ordonnance', '0'),
        ];

        if (empty($data['nom']) || strlen($data['nom']) < 2) {
            $errors[] = 'Le nom du medicament est obligatoire (2 caracteres minimum).';
        }
        if (empty($data['categorie'])) {
            $errors[] = 'La categorie est obligatoire.';
        }
        if (!is_numeric($data['prix']) || (float) $data['prix'] < 0) {
            $errors[] = 'Le prix doit etre un nombre positif.';
        }
        if (!ctype_digit((string) $data['stock']) || (int) $data['stock'] < 0) {
            $errors[] = 'Le stock doit etre un entier positif ou nul.';
        }
        if (!ctype_digit((string) $data['stock_minimum']) || (int) $data['stock_minimum'] < 0) {
            $errors[] = 'Le seuil de stock minimum doit etre un entier positif ou nul.';
        }
        if (!empty($data['date_expiration'])) {
            $d = DateTime::createFromFormat('Y-m-d', $data['date_expiration']);
            if (!$d) {
                $errors[] = 'Date d expiration invalide.';
            }
        }

        $data['necessite_ordonnance'] = $data['necessite_ordonnance'] === '1' ? 1 : 0;

        return $data;
    }
}
