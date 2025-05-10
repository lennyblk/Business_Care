@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Détails de la facture #{{ $invoice->invoice_number }}</h1>
        <div>
            <a href="{{ route('admin.invoices.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
            <a href="{{ route('admin.invoices.download', $invoice->id) }}" class="btn btn-primary ml-2">
                <i class="fas fa-download"></i> Télécharger
            </a>
            @if($invoice->status !== 'paid')
            <form action="{{ route('admin.invoices.mark-as-paid', $invoice->id) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success ml-2" onclick="return confirm('Marquer cette facture comme payée?')">
                    <i class="fas fa-check"></i> Marquer comme payée
                </button>
            </form>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informations de la facture</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 40%">Numéro de facture</th>
                                <td>{{ $invoice->invoice_number }}</td>
                            </tr>
                            <tr>
                                <th>Date d'émission</th>
                                <td>{{ \Carbon\Carbon::parse($invoice->issue_date)->format('d/m/Y') }}</td>
                            </tr>
                            <tr>
                                <th>Date d'échéance</th>
                                <td>{{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}</td>
                            </tr>
                            @if(isset($invoice->period_start) && isset($invoice->period_end))
                            <tr>
                                <th>Période facturée</th>
                                <td>{{ \Carbon\Carbon::parse($invoice->period_start)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($invoice->period_end)->format('d/m/Y') }}</td>
                            </tr>
                            @endif
                            <tr>
                                <th>Statut</th>
                                <td>
                                    @if($invoice->status === 'paid')
                                        <span class="badge bg-success">Payée</span>
                                    @elseif($invoice->status === 'pending')
                                        <span class="badge bg-warning">En attente</span>
                                    @elseif($invoice->status === 'overdue')
                                        <span class="badge bg-danger">En retard</span>
                                    @endif
                                </td>
                            </tr>
                            @if($invoice->status === 'paid' && isset($invoice->payment_date))
                            <tr>
                                <th>Date de paiement</th>
                                <td>{{ \Carbon\Carbon::parse($invoice->payment_date)->format('d/m/Y') }}</td>
                            </tr>
                            @endif
                            @if($invoice->status === 'paid' && isset($invoice->payment_method))
                            <tr>
                                <th>Méthode de paiement</th>
                                <td>{{ $invoice->payment_method }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informations du client</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 40%">Entreprise</th>
                                <td>
                                    <a href="{{ route('admin.company.show', $invoice->company_id) }}">
                                        {{ $invoice->company->name }}
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <th>Adresse</th>
                                <td>{{ $invoice->company->address }}</td>
                            </tr>
                            <tr>
                                <th>Code postal</th>
                                <td>{{ $invoice->company->code_postal }}</td>
                            </tr>
                            <tr>
                                <th>Ville</th>
                                <td>{{ $invoice->company->ville }}</td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td>{{ $invoice->company->email }}</td>
                            </tr>
                            <tr>
                                <th>Téléphone</th>
                                <td>{{ $invoice->company->telephone }}</td>
                            </tr>
                            @if(isset($invoice->company->siret))
                            <tr>
                                <th>SIRET</th>
                                <td>{{ $invoice->company->siret }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Détails de la facture</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th>Quantité</th>
                            <th class="text-right">Prix unitaire</th>
                            <th class="text-right">Montant HT</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Abonnement {{ $invoice->contract->formule_abonnement }}</td>
                            <td>1</td>
                            <td class="text-right">{{ number_format($invoice->amount * 0.8, 2, ',', ' ') }} €</td>
                            <td class="text-right">{{ number_format($invoice->amount * 0.8, 2, ',', ' ') }} €</td>
                        </tr>
                        <tr>
                            <td>Services inclus</td>
                            <td>1</td>
                            <td class="text-right">{{ number_format($invoice->amount * 0.2, 2, ',', ' ') }} €</td>
                            <td class="text-right">{{ number_format($invoice->amount * 0.2, 2, ',', ' ') }} €</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-right">Total HT</th>
                            <td class="text-right">{{ number_format($invoice->amount, 2, ',', ' ') }} €</td>
                        </tr>
                        <tr>
                            <th colspan="3" class="text-right">TVA (20%)</th>
                            <td class="text-right">{{ number_format($invoice->amount * 0.2, 2, ',', ' ') }} €</td>
                        </tr>
                        <tr>
                            <th colspan="3" class="text-right">Total TTC</th>
                            <td class="text-right font-weight-bold">{{ number_format($invoice->amount * 1.2, 2, ',', ' ') }} €</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Contrat associé</h6>
            <a href="{{ route('admin.contracts.show', $invoice->contract_id) }}" class="btn btn-primary btn-sm">
                <i class="fas fa-eye"></i> Voir le contrat
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <tr>
                        <th style="width: 25%">ID du contrat</th>
                        <td>#{{ $invoice->contract_id }}</td>
                        <th style="width: 25%">Formule</th>
                        <td>{{ $invoice->contract->formule_abonnement }}</td>
                    </tr>
                    <tr>
                        <th>Date de début</th>
                        <td>{{ \Carbon\Carbon::parse($invoice->contract->start_date)->format('d/m/Y') }}</td>
                        <th>Date de fin</th>
                        <td>{{ \Carbon\Carbon::parse($invoice->contract->end_date)->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <th>Montant mensuel</th>
                        <td>{{ number_format($invoice->contract->amount, 2, ',', ' ') }} €</td>
                        <th>Statut</th>
                        <td>
                            @if($invoice->contract->payment_status === 'active')
                                <span class="badge bg-success">Actif</span>
                            @elseif($invoice->contract->payment_status === 'pending')
                                <span class="badge bg-warning">En attente</span>
                            @elseif($invoice->contract->payment_status === 'unpaid')
                                <span class="badge bg-danger">Non payé</span>
                            @else
                                <span class="badge bg-secondary">{{ $invoice->contract->payment_status }}</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
