<?php
require_once __DIR__ . '/../models/Medicament.php';
require_once __DIR__ . '/../models/Ordonnance.php';
require_once __DIR__ . '/../models/Transaction.php';
require_once __DIR__ . '/../models/Utilisateur.php';

class DashboardController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();

        $medicamentModel = new Medicament();
        $ordonnanceModel = new Ordonnance();
        $transactionModel = new Transaction();

        switch (Auth::role()) {
            case 'responsable':
                $data = [
                    'nbMedicaments'    => count($medicamentModel->all()),
                    'stockCritique'    => $medicamentModel->stockCritique(),
                    'nbPerimes'        => $medicamentModel->countPerimes(),
                    'bientotPerimes'   => $medicamentModel->bientotPerimes(90),
                    'ordonnancesAttente' => $ordonnanceModel->countByStatut('en_attente'),
                    'expeditionsRecentes' => $transactionModel->recentes(7),
                    'utilisateurs'     => (new Utilisateur())->all(),
                ];
                $this->render('responsable/dashboard', $data);
                break;

            case 'pharmacien':
                $data = [
                    'ordonnancesAttente' => $ordonnanceModel->byStatut('en_attente'),
                    'mesTransactions'    => $transactionModel->byPharmacien(Auth::id()),
                ];
                $this->render('pharmacien/dashboard', $data);
                break;

            case 'client':
                $data = [
                    'mesOrdonnances'  => $ordonnanceModel->byClient(Auth::id()),
                    'mesAchats'       => $transactionModel->byClient(Auth::id()),
                    'medicaments'     => $medicamentModel->disponiblesPourClient(),
                ];
                $this->render('client/dashboard', $data);
                break;

            default:
                Auth::logout();
                $this->redirect('index.php?c=auth&a=login');
        }
    }
}
