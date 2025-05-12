@extends('layouts.admin')

@section('title', 'Détails du contrat')

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
                    <a href="{{ route('contracts.index') }}" class="list-group-item list-group-item-action active">Contrats</a>
                    <a href="{{ route('quotes.index') }}" class="list-group-item list-group-item-action">Devis</a>
                    <a href="{{ route('employees.index') }}" class="list-group-item list-group-item-action">Collaborateurs</a>
                    <a href="{{ route('invoices.index') }}" class="list-group-item list-group-item-action">Facturation</a>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Détails du contrat #{{ $contract->id }}</h4>
                    <div>
                        <a href="{{ route('contracts.index') }}" class="btn btn-secondary me-2">
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
                                            <th>Statut:</th>
                                            <td>
                                                @if($contract->payment_status === 'pending')
                                                    <span class="badge bg-warning">En attente d'approbation</span>
                                                @elseif($contract->payment_status === 'unpaid')
                                                    <span class="badge bg-danger">Non payé</span>
                                                @elseif($contract->payment_status === 'processing')
                                                    <span class="badge bg-info">Paiement en cours</span>
                                                @elseif($contract->payment_status === 'active')
                                                    <span class="badge bg-success">Actif</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Date de début:</th>
                                            <td>{{ \Carbon\Carbon::parse($contract->start_date)->format('d/m/Y') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Date de fin:</th>
                                            <td>{{ \Carbon\Carbon::parse($contract->end_date)->format('d/m/Y') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Durée:</th>
                                            <td>{{ \Carbon\Carbon::parse($contract->start_date)->diffInMonths($contract->end_date) }} mois</td>
                                        </tr>
                                        <tr>
                                            <th>Méthode de paiement:</th>
                                            <td>{{ $contract->payment_method }}</td>
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
                                            <th>Montant:</th>
                                            <td class="fw-bold">{{ number_format($contract->amount, 2, ',', ' ') }} €</td>
                                        </tr>
                                        <tr>
                                            <th>Fréquence:</th>
                                            <td>Mensuelle</td>
                                        </tr>
                                        <tr>
                                            <th>Total du contrat:</th>
                                            <td class="fw-bold text-primary">
                                                {{ number_format($contract->amount * \Carbon\Carbon::parse($contract->start_date)->diffInMonths($contract->end_date), 2, ',', ' ') }} €
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Prochain paiement:</th>
                                            <td>
                                                @if($contract->payment_status === 'active')
                                                    {{ \Carbon\Carbon::now()->addMonth()->format('d/m/Y') }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Détails des services -->
                    <div class="card mb-4">
                        <div class="card-header">
                            Services inclus
                        </div>
                        <div class="card-body">
                            <div class="services-description">
                                {!! nl2br(e($contract->services)) !!}
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
                                @if($contract->payment_status === 'pending')
                                    <div class="col-12 text-center">
                                        <p class="text-muted">En attente de validation par l'administrateur</p>
                                    </div>
                                @elseif($contract->payment_status === 'unpaid')
                                    <div class="col-md-4 mb-3">
                                        <a href="{{ route('contracts.payment.create', $contract->id) }}" class="btn btn-success w-100">
                                            <i class="bi bi-credit-card"></i> Payer le contrat
                                        </a>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <a href="{{ route('contracts.edit', $contract->id) }}" class="btn btn-warning w-100">
                                            <i class="bi bi-pencil"></i> Modifier le contrat
                                        </a>
                                    </div>
                                @elseif($contract->payment_status === 'processing')
                                    <div class="col-12 text-center">
                                        <p class="text-info">Paiement en cours de traitement</p>
                                    </div>
                                @elseif($contract->payment_status === 'active')
                                    <div class="col-md-4 mb-3">
                                        <a href="{{ route('contracts.download', $contract->id) }}" class="btn btn-outline-primary w-100">
                                            <i class="bi bi-file-pdf"></i> Télécharger le contrat
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="{{ route('contracts.request-change', $contract->id) }}" class="btn btn-outline-info w-100">
                                            <i class="bi bi-arrow-repeat"></i> Changer de formule
                                        </a>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <button type="button" class="btn btn-outline-danger w-100"
                                                data-bs-toggle="modal"
                                                data-bs-target="#terminateModal">
                                            <i class="bi bi-x-circle"></i> Demander une résiliation
                                        </button>
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

@if($contract->payment_status === 'active')
<div class="modal fade" id="terminateModal" tabindex="-1" aria-labelledby="terminateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="terminateModalLabel">Confirmation de résiliation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir demander la résiliation de ce contrat ?</p>
                <p><strong>Services :</strong> {{ $contract->services }}</p>
                <p><strong>Date de fin initiale :</strong> {{ \Carbon\Carbon::parse($contract->end_date)->format('d/m/Y') }}</p>
                <p class="text-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                    Attention : Des frais de résiliation anticipée peuvent s'appliquer selon les termes du contrat.
                </p>
                <p>Un conseiller vous contactera pour finaliser cette demande.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form action="{{ route('contracts.terminate', $contract->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-danger">Confirmer la demande</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
@endsection
