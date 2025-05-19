@extends('layouts.admin')

@section('title', 'Détails de la facture')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-md-3">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    Menu
                </div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('dashboard.client') }}" class="list-group-item list-group-item-action">Tableau de bord</a>
                    <a href="{{ route('profile.index') }}" class="list-group-item list-group-item-action">Profil</a>
                    <a href="{{ route('contracts.index') }}" class="list-group-item list-group-item-action">Contrats</a>
                    <a href="{{ route('quotes.index') }}" class="list-group-item list-group-item-action">Devis</a>
                    <a href="{{ route('employees.index') }}" class="list-group-item list-group-item-action">Collaborateurs</a>
                    <a href="{{ route('invoices.index') }}" class="list-group-item list-group-item-action">Facturation</a>
                    <a href="{{ route('client.event_proposals.index') }}" class="list-group-item list-group-item-action">Demande d'activités</a>
                    <a href="{{ route('client.associations.index') }}" class="list-group-item list-group-item-action">Associations</a>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Facture #{{ $invoice->invoice_number ?? 'F-'.$invoice->id }}</h4>
                    <div>
                        <a href="{{ route('invoices.index') }}" class="btn btn-secondary me-2">
                            <i class="fas fa-arrow-left"></i> Retour
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Alertes et notifications -->
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

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header">
                                    Informations générales
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th>Numéro de facture:</th>
                                            <td>{{ $invoice->invoice_number ?? 'F-'.$invoice->id }}</td>
                                        </tr>
                                        <tr>
                                            <th>Date d'émission:</th>
                                            <td>{{ \Carbon\Carbon::parse($invoice->issue_date)->format('d/m/Y') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Date d'échéance:</th>
                                            <td>{{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}</td>
                                        </tr>
                                        @if(isset($invoice->period_start) && isset($invoice->period_end))
                                        <tr>
                                            <th>Période facturée:</th>
                                            <td>{{ \Carbon\Carbon::parse($invoice->period_start)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($invoice->period_end)->format('d/m/Y') }}</td>
                                        </tr>
                                        @endif
                                        <tr>
                                            <th>Statut:</th>
                                            <td>
                                                @if($invoice->payment_status === 'Paid')
                                                    <span class="badge bg-success">Payée</span>
                                                @elseif($invoice->payment_status === 'Pending')
                                                    <span class="badge bg-warning">En attente</span>
                                                @elseif($invoice->payment_status === 'Overdue')
                                                    <span class="badge bg-danger">En retard</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header">
                                    Informations financières
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th>Montant total:</th>
                                            <td class="fw-bold text-primary">
                                                {{ number_format($invoice->total_amount, 2, ',', ' ') }} €
                                            </td>
                                        </tr>
                                        @if($invoice->contract_id)
                                        <tr>
                                            <th>Contrat associé:</th>
                                            <td>
                                                <a href="{{ route('contracts.show', ['contract' => $invoice->contract_id]) }}">
                                                    Contrat #{{ $invoice->contract_id }}
                                                </a>
                                            </td>
                                        </tr>
                                        @endif
                                        @if($invoice->payment_status === 'Paid')
                                        <tr>
                                            <th>Date de paiement:</th>
                                            <td>{{ isset($invoice->issue_date) ? \Carbon\Carbon::parse($invoice->payment_date)->format('d/m/Y') : 'N/A' }}</td>
                                        </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Détails des services facturés -->
                    <div class="card mb-4">
                        <div class="card-header">
                            @if($invoice->is_donation)
                                Détails du don
                            @else
                                Services facturés
                            @endif
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Description</th>
                                            <th>Quantité</th>
                                            <th class="text-end">Prix unitaire</th>
                                            <th class="text-end">Montant</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if($invoice->is_donation)
                                        <tr>
                                            <td>Don à l'association</td>
                                            <td>1</td>
                                            <td class="text-end">{{ number_format($invoice->total_amount, 2, ',', ' ') }} €</td>
                                            <td class="text-end">{{ number_format($invoice->total_amount, 2, ',', ' ') }} €</td>
                                        </tr>
                                        @else
                                        <tr>
                                            <td>{{ $invoice->contract->formule_abonnement }}</td>
                                            <td>1</td>
                                            <td class="text-end">{{ number_format($invoice->total_amount, 2, ',', ' ') }} €</td>
                                            <td class="text-end">{{ number_format($invoice->total_amount, 2, ',', ' ') }} €</td>
                                        </tr>
                                        @endif
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="3" class="text-end">Montant total</th>
                                            <td class="text-end fw-bold">{{ number_format($invoice->total_amount, 2, ',', ' ') }} €</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- Actions disponibles -->
                    <div class="card">
                        <div class="card-header">
                            Actions
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <a href="{{ route('invoices.download', $invoice->id) }}" class="btn btn-primary w-100">
                                        <i class="fas fa-download"></i> Télécharger la facture
                                    </a>
                                </div>

                                @if($invoice->payment_status !== 'Paid' && session('user_type') === 'societe')
                                <div class="col-md-4 mb-3">
                                    <form action="{{ route('invoices.pay', $invoice->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-success w-100">
                                            <i class="fas fa-credit-card"></i> Payer maintenant
                                        </button>
                                    </form>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    /* Badges */
    .badge {
        display: inline-block;
        padding: 0.35em 0.65em;
        font-size: 0.75em;
        font-weight: 700;
        line-height: 1;
        color: #fff;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border-radius: 0.25rem;
    }

    .bg-success {
        background-color: #198754 !important;
    }

    .bg-warning {
        background-color: #ffc107 !important;
        color: #000;
    }

    .bg-danger {
        background-color: #dc3545 !important;
    }

    .text-end {
        text-align: right !important;
    }

    .fw-bold {
        font-weight: 700 !important;
    }

    .me-2 {
        margin-right: 0.5rem !important;
    }

    .text-primary {
        color: #0d6efd !important;
    }
</style>
@endsection
