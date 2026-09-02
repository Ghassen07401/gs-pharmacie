<?php
require_once __DIR__ . '/../models/Ordonnance.php';
require_once __DIR__ . '/../models/Medicament.php';
require_once __DIR__ . '/../models/Interaction.php';
require_once __DIR__ . '/../models/Transaction.php';

class OrdonnanceController extends Controller
{
    private Ordonnance $model;
    private Medicament $medicamentModel;
    private Interaction $interactionModel;

    public function __construct()
    {
        $this->model = new Ordonnance();
        $this->medicamentModel = new Medicament();
        $this->interactionModel = new Interaction();
    }

    /** Liste globale, visible par Responsable et Pharmacien */
    public function index(): void
    {
        Auth::requireRole(['responsable', 'pharmacien']);
        $statut = $this->query('statut', '');
        $ordonnances = $statut !== '' ? $this->model->byStatut($statut) : $this->model->all();
        $this->render('pharmacien/ordonnances_liste', ['ordonnances' => $ordonnances, 'statut' => $statut]);
    }

    /** Formulaire de soumission (Client) */
    public function soumettre(): void
    {
        Auth::requireRole(['client']);
        $errors = [];
        $interactionsDetectees = [];

        $medicamentsDisponibles = $this->medicamentModel->disponiblesPourClient();

        if ($this->isPost()) {
            $this->requireCsrf();

            $medecinNom = $this->input('medecin_nom');
            $datePrescription = $this->input('date_prescription');
            $type = $this->input('type', 'nouvelle');
            $medicamentIds = $_POST['medicament_id'] ?? [];
            $quantites = $_POST['quantite'] ?? [];
            $posologies = $_POST['posologie'] ?? [];

            if (empty($medecinNom)) {
                $errors[] = 'Le nom du medecin prescripteur est obligatoire.';
            }
            if (empty($datePrescription) || !DateTime::createFromFormat('Y-m-d', $datePrescription)) {
                $errors[] = 'La date de prescription est invalide.';
            }
            if (empty($medicamentIds)) {
                $errors[] = 'Veuillez selectionner au moins un medicament.';
            }

            $items = [];
            foreach ($medicamentIds as $i => $mid) {
                $qte = (int) ($quantites[$i] ?? 0);
                if ($mid === '' || $qte < 1) {
                    continue;
                }
                $items[] = [
                    'medicament_id' => (int) $mid,
                    'quantite'      => $qte,
                    'posologie'     => trim($posologies[$i] ?? ''),
                ];
            }
            if (empty($items)) {
                $errors[] = 'Veuillez indiquer une quantite valide pour au moins un medicament.';
            }

            // Verification des interactions medicamenteuses potentielles
            if (!empty($items)) {
                $ids = array_column($items, 'medicament_id');
                $interactionsDetectees = $this->interactionModel->checkForMedicaments($ids);
            }

            if (empty($errors)) {
                $ordonnanceId = $this->model->create([
                    'client_id'         => Auth::id(),
                    'medecin_nom'       => $medecinNom,
                    'date_prescription' => $datePrescription,
                    'type'              => $type === 'renouvellement' ? 'renouvellement' : 'nouvelle',
                    'commentaire'       => null,
                ], $items);

                $this->flash('success', 'Ordonnance soumise avec succes. Elle est en attente de validation.');
                $this->redirect('index.php?c=ordonnance&a=voir&id=' . $ordonnanceId);
            }
        }

        $this->render('client/ordonnance_form', [
            'errors'       => $errors,
            'medicaments'  => $medicamentsDisponibles,
            'interactions' => $interactionsDetectees,
        ]);
    }

    public function voir(array $params): void
    {
        Auth::requireLogin();
        $id = (int) ($params['id'] ?? 0);
        $ordonnance = $this->model->findById($id);

        if (!$ordonnance) {
            $this->flash('error', 'Ordonnance introuvable.');
            $this->redirect('index.php?c=dashboard&a=index');
        }

        // Un client ne peut voir que ses propres ordonnances
        if (Auth::role() === 'client' && (int) $ordonnance['client_id'] !== Auth::id()) {
            http_response_code(403);
            include __DIR__ . '/../views/errors/403.php';
            exit;
        }

        $items = $this->model->items($id);
        $ids = array_column($items, 'medicament_id');
        $interactions = $this->interactionModel->checkForMedicaments($ids);

        $view = Auth::role() === 'client' ? 'client/ordonnance_detail' : 'pharmacien/ordonnance_detail';
        $this->render($view, ['ordonnance' => $ordonnance, 'items' => $items, 'interactions' => $interactions]);
    }

    /**
     * Validation par le Pharmacien ou le Responsable.
     * Trois garanties :
     *  - une ordonnance deja traitee ne peut pas etre validee une seconde fois ;
     *  - le stock disponible est verifie avant toute ecriture ;
     *  - si la generation de l'expedition echoue, le statut est remis en attente.
     */
    public function valider(array $params): void
    {
        Auth::requireRole(['responsable', 'pharmacien']);
        $id = (int) ($params['id'] ?? 0);
        $ordonnance = $this->model->findById($id);

        if (!$ordonnance) {
            $this->flash('error', 'Ordonnance introuvable.');
            $this->redirect('index.php?c=ordonnance&a=index');
        }

        if ($ordonnance['statut'] !== 'en_attente') {
            $this->flash('error', 'Cette ordonnance a deja ete traitee (statut : ' . $ordonnance['statut'] . ').');
            $this->redirect('index.php?c=ordonnance&a=voir&id=' . $id);
        }

        $items = $this->model->items($id);

        // Controle de disponibilite du stock (affiche sur le formulaire, bloquant a la soumission)
        $rupture = [];
        foreach ($items as $it) {
            if ((int) $it['stock'] < (int) $it['quantite']) {
                $rupture[] = $it['medicament_nom'] . ' (demande : ' . (int) $it['quantite']
                           . ', stock : ' . (int) $it['stock'] . ')';
            }
        }

        // Controle de peremption : un medicament perime ne doit jamais etre delivre,
        // meme si le stock physique est suffisant. Regle metier non negociable.
        $perimes = [];
        foreach ($items as $it) {
            if (Medicament::etatPeremption($it['date_expiration']) === 'perime') {
                $perimes[] = $it['medicament_nom'] . ' (expire le '
                           . date('d/m/Y', strtotime($it['date_expiration'])) . ')';
            }
        }

        // Le pharmacien est alerte des interactions au moment de la validation
        $interactions = $this->interactionModel->checkForMedicaments(array_column($items, 'medicament_id'));

        if ($this->isPost()) {
            $this->requireCsrf();

            if (!empty($perimes)) {
                $this->flash('error', 'Delivrance impossible, medicament perime : ' . implode(' ; ', $perimes));
                $this->redirect('index.php?c=ordonnance&a=valider&id=' . $id);
            }

            if (!empty($rupture)) {
                $this->flash('error', 'Stock insuffisant : ' . implode(' ; ', $rupture));
                $this->redirect('index.php?c=ordonnance&a=valider&id=' . $id);
            }

            $commentaire = $this->input('commentaire', '');

            // Bascule atomique du statut : false si une autre session a deja traite l'ordonnance
            if (!$this->model->valider($id, Auth::id(), $commentaire)) {
                $this->flash('error', 'Cette ordonnance vient d etre traitee par un autre utilisateur.');
                $this->redirect('index.php?c=ordonnance&a=index');
            }

            $transactionItems = array_map(fn($it) => [
                'medicament_id' => $it['medicament_id'],
                'quantite'      => $it['quantite'],
                'prix'          => $it['prix'],
            ], $items);

            if (!empty($transactionItems)) {
                try {
                    (new Transaction())->createFromOrdonnance(
                        $id,
                        (int) $ordonnance['client_id'],
                        Auth::id(),
                        $transactionItems
                    );
                } catch (Exception $e) {
                    // Compensation : l'expedition a echoue, l'ordonnance repasse en attente
                    $this->model->annulerValidation($id);
                    $this->flash('error', 'Validation annulee : ' . $e->getMessage());
                    $this->redirect('index.php?c=ordonnance&a=valider&id=' . $id);
                }
            }

            $this->flash('success', 'Ordonnance validee et expedition enregistree.');
            $this->redirect('index.php?c=ordonnance&a=index');
        }

        $this->render('pharmacien/ordonnance_valider', [
            'ordonnance'   => $ordonnance,
            'items'        => $items,
            'interactions' => $interactions,
            'rupture'      => $rupture,
            'perimes'      => $perimes,
        ]);
    }

    public function refuser(array $params): void
    {
        Auth::requireRole(['responsable', 'pharmacien']);
        $id = (int) ($params['id'] ?? 0);
        $ordonnance = $this->model->findById($id);

        if (!$ordonnance) {
            $this->flash('error', 'Ordonnance introuvable.');
            $this->redirect('index.php?c=ordonnance&a=index');
        }

        if ($ordonnance['statut'] !== 'en_attente') {
            $this->flash('error', 'Cette ordonnance a deja ete traitee (statut : ' . $ordonnance['statut'] . ').');
            $this->redirect('index.php?c=ordonnance&a=voir&id=' . $id);
        }

        if ($this->isPost()) {
            $this->requireCsrf();

            $commentaire = $this->input('commentaire', '');
            if ($this->model->refuser($id, Auth::id(), $commentaire)) {
                $this->flash('success', 'Ordonnance refusee.');
            } else {
                $this->flash('error', 'Cette ordonnance vient d etre traitee par un autre utilisateur.');
            }
            $this->redirect('index.php?c=ordonnance&a=index');
        }

        $this->render('pharmacien/ordonnance_refuser', ['ordonnance' => $ordonnance]);
    }

    /** Historique du client */
    public function historique(): void
    {
        Auth::requireRole(['client']);
        $this->render('client/ordonnances_historique', ['ordonnances' => $this->model->byClient(Auth::id())]);
    }

    /** Suppression d'une ordonnance (Responsable uniquement, ex: doublon ou erreur de saisie) */
    public function supprimer(array $params): void
    {
        Auth::requireRole(['responsable']);
        $this->requirePostCsrf();
        $id = (int) ($params['id'] ?? 0);
        $this->model->delete($id);
        $this->flash('success', 'Ordonnance supprimee.');
        $this->redirect('index.php?c=ordonnance&a=index');
    }
}
