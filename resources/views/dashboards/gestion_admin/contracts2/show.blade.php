@extends('layouts.admin')

@section('title', 'Détails du contrat')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-md-3">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    Menu Administrateur
                </div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('admin.company') }}" class="list-group-item list-group-item-action">Entreprises</a>
                    <a href="{{ route('admin.salaries.index') }}" class="list-group-item list-group-item-action">Salariés</a>
                    <a href="{{ route('admin.prestataires.index') }}" class="list-group-item list-group-item-action">Prestataires</a>
                    <a href="{{ route('admin.activities.index') }}" class="list-group-item list-group-item-action">Activités</a>
                    <a href="{{ route('admin.inscriptions.index') }}" class="list-group-item list-group-item-action">Inscriptions en attente</a>
                    <a href="{{ route('admin.contracts.index') }}" class="list-group-item list-group-item-action">Contrats en attente</a>
                    <a href="{{ route('admin.contracts2.index') }}" class="list-group-item list-group-item-action">Contrats</a>
                    <a href="{{ route('admin.event_proposals.index') }}" class="list-group-item list-group-item-action">Demandes d'activités</a>
                    <a href="{{ route('admin.invoices.index') }}" class="list-group-item list-group-item-action">Facturation</a>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Détails du contrat #{{ $contract->id }}</h4>
                    <div>
                        <a href="{{ route('admin.contracts2.download', $contract->id) }}" class="btn btn-sm btn-success me-2">
                            <i class="bi bi-file-earmark-pdf"></i> Télécharger
                        </a>
                        <a href="{{ route('admin.contracts2.index') }}" class="btn btn-sm btn-secondary">
                            <i class="bi bi-arrow-left"></i> Retour à la liste
                        </a>
                    </div>
                </div>
                <div class="card-body">
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
                        <div class="col-md-6">
                            <h5>Informations du contrat</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th>ID</th>
                                    <td>{{ $contract->id }}</td>
                                </tr>
                                <tr>
                                    <th>Date de début</th>
                                    <td>{{ $contract->start_date }}</td>
                                </tr>
                                <tr>
                                    <th>Date de fin</th>
                                    <td>{{ $contract->end_date }}</td>
                                </tr>
                                <tr>
                                    <th>Montant</th>
                                    <td>{{ $contract->amount }} €</td>
                                </tr>
                                <tr>
                                    <th>Méthode de paiement</th>
                                    <td>{{ $contract->payment_method }}</td>
                                </tr>
                                <tr>
                                    <th>Statut du paiement</th>
                                    <td>
                                        @if($contract->payment_status == 'pending')
                                            <span class="badge bg-warning">En attente</span>
                                        @elseif($contract->payment_status == 'paid')
                                            <span class="badge bg-success">Payé</span>
                                        @elseif($contract->payment_status == 'unpaid')
                                            <span class="badge bg-danger">Non payé</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $contract->payment_status }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Formule d'abonnement</th>
                                    <td>{{ $contract->formule_abonnement }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Informations de l'entreprise</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th>Nom</th>
                                    <td>{{ $contract->company->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td>{{ $contract->company->email ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Téléphone</th>
                                    <td>{{ $contract->company->telephone ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Adresse</th>
                                    <td>{{ $contract->company->address ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h5>Services inclus</h5>
                        <div class="p-3 border rounded">
                            {!! nl2br(e($contract->services ?? 'Aucun service spécifié')) !!}
                        </div>
                    </div>

                    @if($contract->payment_status == 'unpaid')
                    <div class="mt-4">
                        <form action="{{ route('admin.contracts2.mark-as-paid', $contract->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success" onclick="return confirm('Êtes-vous sûr de vouloir marquer ce contrat comme payé ?')">
                                <i class="bi bi-check-circle"></i> Marquer comme payé
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

