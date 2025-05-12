@extends('layouts.admin')

@section('title', 'Gestion des contrats')

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
                    <h4 class="mb-0">Gestion des contrats</h4>
                    <a href="{{ route('contracts.create') }}" class="btn btn-primary">Nouveau contrat</a>
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

                    @if(session('info'))
                    <div class="alert alert-info">
                        {{ session('info') }}
                    </div>
                    @endif

                    @if(empty($contracts))
                        <div class="alert alert-info">
                            Vous n'avez pas encore de contrats. Cliquez sur "Nouveau contrat" pour en créer un.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Services</th>
                                        <th>Début</th>
                                        <th>Fin</th>
                                        <th>Montant</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($contracts as $contract)
                                    <tr>
                                        <td>{{ $contract->id }}</td>
                                        <td>{{ Str::limit($contract->services, 30) }}</td>
                                        <td>{{ \Carbon\Carbon::parse($contract->start_date)->format('d/m/Y') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($contract->end_date)->format('d/m/Y') }}</td>
                                        <td>{{ number_format($contract->amount, 2, ',', ' ') }} €</td>
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

                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('contracts.show', $contract->id) }}" class="btn btn-info">
                                                    <i class="bi bi-eye"></i> Voir
                                                </a>

                                                @if($contract->payment_status === 'unpaid')
                                                    <a href="{{ route('contracts.payment.create', $contract->id) }}" class="btn btn-success">
                                                        <i class="bi bi-credit-card"></i> Payer
                                                    </a>
                                                @elseif($contract->payment_status === 'active')
                                                    <button type="button" class="btn btn-danger"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#deleteModal{{ $contract->id }}">
                                                        <i class="bi bi-trash"></i> Résilier
                                                    </button>
                                                @elseif($contract->payment_status === 'pending')
                                                    <span class="text-muted"></span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@foreach($contracts as $contract)
<div class="modal fade" id="deleteModal{{ $contract->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $contract->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel{{ $contract->id }}">Confirmation de résiliation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if($contract->payment_status === 'active')
                    <p>Êtes-vous sûr de vouloir demander la résiliation de ce contrat ?</p>
                    <p><strong>Services :</strong> {{ $contract->services }}</p>
                    <p><strong>Date de fin :</strong> {{ \Carbon\Carbon::parse($contract->end_date)->format('d/m/Y') }}</p>
                    <p class="text-danger">Note : Un conseiller vous contactera pour finaliser cette demande.</p>
                @else
                    <p>Êtes-vous sûr de vouloir supprimer ce contrat ?</p>
                    <p><strong>Services :</strong> {{ $contract->services }}</p>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>

                @if($contract->payment_status === 'active')
                    <!-- Formulaire de résiliation pour les contrats actifs -->
                    <form action="{{ route('contracts.terminate', $contract->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-danger">Confirmer</button>
                    </form>
                @else
                    <!-- Formulaire de suppression pour les contrats non actifs -->
                    <form action="{{ route('contracts.destroy', $contract->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Confirmer</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endforeach
@endsection
