<?php
require_once __DIR__ . '/../models/Transaction.php';

class TransactionController extends Controller
{
    private Transaction $model;

    public function __construct()
    {
        $this->model = new Transaction();
    }

    /** Historique des transactions du pharmacien connecte */
    public function mesTransactions(): void
    {
        Auth::requireRole(['pharmacien']);
        $this->render('pharmacien/transactions_historique', ['transactions' => $this->model->byPharmacien(Auth::id())]);
    }

    /** Historique des achats du client connecte */
    public function historiqueAchats(): void
    {
        Auth::requireRole(['client']);
        $this->render('client/achats_historique', ['transactions' => $this->model->byClient(Auth::id())]);
    }

    public function voir(array $params): void
    {
        Auth::requireLogin();
        $id = (int) ($params['id'] ?? 0);
        $transaction = $this->model->findById($id);

        if (!$transaction) {
            $this->flash('error', 'Transaction introuvable.');
            $this->redirect('index.php?c=dashboard&a=index');
        }

        if (Auth::role() === 'client' && (int) $transaction['client_id'] !== Auth::id()) {
            http_response_code(403);
            include __DIR__ . '/../views/errors/403.php';
            exit;
        }
        if (Auth::role() === 'pharmacien' && (int) $transaction['pharmacien_id'] !== Auth::id()) {
            http_response_code(403);
            include __DIR__ . '/../views/errors/403.php';
            exit;
        }

        $this->render('shared/transaction_detail', [
            'transaction' => $transaction,
            'details'     => $this->model->details($id),
        ]);
    }

    /** Export CSV du rapport des expeditions (Responsable) */
    public function exportExpeditionsCsv(): void
    {
        Auth::requireRole(['responsable']);

        $jours = (int) $this->query('jours', 30);
        $jours = $jours > 0 ? $jours : 30;

        $lignes = array_map(fn($t) => [
            $t['id'],
            $t['client_nom'],
            $t['pharmacien_nom'],
            number_format((float) $t['montant_total'], 2, ',', ''),
            $t['statut'],
            $t['date_transaction'],
        ], $this->model->recentes($jours));

        $this->exportCsv(
            'expeditions_' . $jours . 'j_' . date('Y-m-d') . '.csv',
            ['Reference', 'Client', 'Pharmacien', 'Montant TND', 'Statut', 'Date'],
            $lignes
        );
    }

    /** Rapport des expeditions (Responsable) */
    public function rapportExpeditions(): void
    {
        Auth::requireRole(['responsable']);
        $jours = (int) $this->query('jours', 30);
        $this->render('responsable/rapport_expeditions', [
            'transactions' => $this->model->recentes($jours ?: 30),
            'jours'        => $jours ?: 30,
        ]);
    }
}
