@extends('layouts.admin')

@section('title', 'Gestion des Contrats')

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
                    <a href="{{ route('admin.contracts2.index') }}" class="list-group-item list-group-item-action active">Contrats</a>
                    <a href="{{ route('admin.event_proposals.index') }}" class="list-group-item list-group-item-action">Demandes d'activités</a>
                    <a href="{{ route('admin.invoices.index') }}" class="list-group-item list-group-item-action">Facturation</a>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Liste de tous les contrats</h4>
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

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Entreprise</th>
                                    <th>Début</th>
                                    <th>Fin</th>
                                    <th>Montant</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($contracts as $contract)
                                    <tr>
                                        <td>{{ $contract->id }}</td>
                                        <td>{{ $contract->company->name ?? 'N/A' }}</td>
                                        <td>{{ $contract->start_date }}</td>
                                        <td>{{ $contract->end_date }}</td>
                                        <td>{{ $contract->amount }} €</td>
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
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('admin.contracts2.show', $contract->id) }}" class="btn btn-sm btn-info">
                                                    <i class="bi bi-eye"></i> Détails
                                                </a>
                                                <a href="{{ route('admin.contracts2.download', $contract->id) }}" class="btn btn-sm btn-success">
                                                    <i class="bi bi-file-earmark-pdf"></i> Télécharger
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">Aucun contrat trouvé</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

