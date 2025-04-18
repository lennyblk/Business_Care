<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Service;
use App\Models\Activity;
use App\Models\Company;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ContractController extends Controller
{
    public function index()
    {
        if (session('user_type') !== 'societe') {
            Log::warning('Tentative accès non autorisé', [
                'user_type' => session('user_type'),
                'route' => 'contracts.index'
            ]);
            return redirect()->route('login')
                ->with('error', 'Accès réservé aux sociétés');
        }

        $companyId = session('user_id');
        $contracts = Contract::with('company')
                           ->where('company_id', $companyId)
                           ->orderBy('start_date', 'desc')
                           ->paginate(10);

        $today = Carbon::today();
        foreach ($contracts as $contract) {
            $contract->is_active = $today->between(
                Carbon::parse($contract->start_date),
                Carbon::parse($contract->end_date)
            );
        }

        return view('dashboards.client.contracts.index', compact('contracts'));
    }

    public function create()
    {
        if (session('user_type') !== 'societe') {
            return redirect()->route('login')
                ->with('error', 'Accès réservé aux sociétés');
        }

        $companyId = session('user_id');
        $company = Company::with('employees')->findOrFail($companyId);

        return view('dashboards.client.contracts.create', [
            'employeeCount' => $company->employees->count(),
            'defaultFormula' => $this->getDefaultFormula($company),
            'company' => $company,
            'services' => Service::active()->get()
        ]);
    }

    public function store(Request $request)
    {
        if (session('user_type') !== 'societe') {
            return redirect()->route('login')
                ->with('error', 'Accès réservé aux sociétés');
        }

        $validated = $request->validate($this->validationRules());

        $contract = Contract::create([
            'company_id' => session('user_id'),
            'services' => $request->services,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'formule_abonnement' => $request->formule_abonnement,
            'statut_contrat' => 'En cours de validation',
        ]);

        Activity::create([
            'company_id' => session('user_id'),
            'user_id' => session('user_id'),
            'title' => 'Nouveau contrat créé',
            'description' => "Contrat #{$contract->id} - {$request->formule_abonnement}",
            'type' => 'contract_creation',
            'subject_id' => $contract->id,
        ]);

        return redirect()->route('contracts.show', $contract->id)
            ->with('success', 'Contrat créé avec succès');
    }

    public function show($id)
    {
        $contract = $this->getAuthorizedContract($id);
        return view('dashboards.client.contracts.show', compact('contract'));
    }

    public function edit($id)
    {
        $contract = $this->getAuthorizedContract($id);
        return view('dashboards.client.contracts.edit', [
            'contract' => $contract,
            'services' => Service::active()->get()
        ]);
    }

    public function update(Request $request, $id)
    {
        $contract = $this->getAuthorizedContract($id);

        $validated = $request->validate([
            'services' => 'required|string',
            'end_date' => 'required|date|after:start_date',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:Direct Debit,Invoice',
        ]);

        $contract->update($validated);

        Activity::create([
            'company_id' => session('user_id'),
            'user_id' => session('user_id'),
            'title' => 'Contrat mis à jour',
            'description' => "Contrat #{$contract->id} modifié",
            'type' => 'contract_update',
            'subject_id' => $contract->id,
        ]);

        return redirect()->route('contracts.show', $contract->id)
            ->with('success', 'Contrat mis à jour');
    }

    public function destroy($id)
    {
        $contract = $this->getAuthorizedContract($id);

        if ($contract->isActive()) {
            Activity::create([
                'company_id' => session('user_id'),
                'user_id' => session('user_id'),
                'title' => 'Résiliation demandée',
                'description' => "Demande résiliation contrat #{$contract->id}",
                'type' => 'contract_termination',
                'subject_id' => $contract->id,
            ]);

            return redirect()->route('contracts.index')
                ->with('info', 'Demande de résiliation enregistrée');
        }

        $contract->delete();
        return redirect()->route('contracts.index')
            ->with('success', 'Contrat supprimé');
    }

    // Méthodes protégées
    protected function getAuthorizedContract($id)
    {
        if (session('user_type') !== 'societe') {
            abort(403, 'Accès non autorisé');
        }

        $contract = Contract::with('company')
                          ->findOrFail($id);

        if ($contract->company_id != session('user_id')) {
            abort(403, 'Ce contrat ne vous appartient pas');
        }

        return $contract;
    }

    protected function validationRules()
    {
        return [
            'services' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:Direct Debit,Invoice',
            'formule_abonnement' => 'required|in:Starter,Basic,Premium',
        ];
    }

    protected function getDefaultFormula(Company $company)
    {
        $count = $company->employees->count();

        if ($count > 250) return 'Premium';
        if ($count > 30) return 'Basic';
        return 'Starter';
    }
}
