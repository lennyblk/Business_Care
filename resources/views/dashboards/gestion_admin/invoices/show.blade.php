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
                    <a href="#" class="list-group-item list-group-item-action">Profil</a>
                    <a href="{{ route('contracts.index') }}" class="list-group-item list-group-item-action">Contrats</a>
                    <a href="{{ route('quotes.index') }}" class="list-group-item list-group-item-action">Devis</a>
                    <a href="{{ route('employees.index') }}" class="list-group-item list-group-item-action">Collaborateurs</a>
                    <a href="{{ route('payments.index') }}" class="list-group-item list-group-item-action">Paiements</a>
                    <a href="{{ route('invoices.index') }}" class="list-group-item list-group-item-action active">Facturation</a>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Facture #{{ $invoice->invoice_number }}</h4>
                    <div>
                        <a href="{{ route('invoices.index') }}" class="btn btn-secondary me-2">
                            <i class="bi bi-arrow-left"></i> Retour
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
                                            <td>{{ $invoice->invoice_number }}</td>
                                        </tr>
                                        <tr>
                                            <th>Date d'émission:</th>
                                            <td>{{ \Carbon\Carbon::parse($invoice->issue_date)->format('d/m/Y') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Date d'échéance:</th>
                                            <td>{{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Statut:</th>
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
                                            <th>Montant HT:</th>
                                            <td>{{ number_format($invoice->amount, 2, ',', ' ') }} €</td>
                                        </tr>
                                        <tr>
                                            <th>TVA (20%):</th>
                                            <td>{{ number_format($invoice->amount * 0.2, 2, ',', ' ') }} €</td>
                                        </tr>
                                        <tr>
                                            <th>Montant TTC:</th>
                                            <td class="fw-bold text-primary">
                                                {{ number_format($invoice->amount * 1.2, 2, ',', ' ') }} €
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Contrat associé:</th>
                                            <td>
                                                <a href="{{ route('contracts.show', $invoice->contract_id) }}">
                                                    Contrat #{{ $invoice->contract_id }}
                                                </a>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
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
                                        <i class="bi bi-download"></i> Télécharger la facture
                                    </a>
                                </div>

                                @if($invoice->status !== 'paid' && session('user_type') === 'societe')
                                <div class="col-md-4 mb-3">
                                    <form action="{{ route('invoices.pay', $invoice->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-success w-100">
                                            <i class="bi bi-credit-card"></i> Payer maintenant
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
