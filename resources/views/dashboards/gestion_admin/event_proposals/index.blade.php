@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Gestion des demandes d'activités</h2>
            <a href="{{ route('dashboard.admin') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Retour au tableau de bord
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="m-0">Demandes en attente ({{ $pendingProposals->count() }})</h5>
                </div>
                <div class="card-body">
                    @if($pendingProposals->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Entreprise</th>
                                    <th>Type d'activité</th>
                                    <th>Date souhaitée</th>
                                    <th>Lieu</th>
                                    <th>Date de demande</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingProposals as $proposal)
                                <tr>
                                    <td>{{ $proposal->id }}</td>
                                    <td>{{ $proposal->company->name }}</td>
                                    <td>{{ $proposal->eventType->title }}</td>
                                    <td>{{ $proposal->proposed_date->format('d/m/Y') }}</td>
                                    <td>{{ $proposal->location->name }} ({{ $proposal->location->city }})</td>
                                    <td>{{ $proposal->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <a href="{{ route('admin.event_proposals.show', $proposal->id) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> Détails
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-info mb-0">
                        Aucune demande d'activité en attente.
                    </div>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="m-0">Demandes assignées/acceptées ({{ $assignedProposals->count() }})</h5>
                </div>
                <div class="card-body">
                    @if($assignedProposals->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Entreprise</th>
                                    <th>Type d'activité</th>
                                    <th>Date</th>
                                    <th>Lieu</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($assignedProposals as $proposal)
                                <tr>
                                    <td>{{ $proposal->id }}</td>
                                    <td>{{ $proposal->company->name }}</td>
                                    <td>{{ $proposal->eventType->title }}</td>
                                    <td>{{ $proposal->proposed_date->format('d/m/Y') }}</td>
                                    <td>{{ $proposal->location->name }} ({{ $proposal->location->city }})</td>
                                    <td>
                                        <span class="badge {{ $proposal->status == 'Assigned' ? 'bg-info' : 'bg-success' }}">
                                            {{ $proposal->status }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.event_proposals.show', $proposal->id) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> Détails
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-info mb-0">
                        Aucune demande d'activité assignée.
                    </div>
                    @endif
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header bg-danger text-white">
                    <h5 class="m-0">Demandes refusées ({{ $rejectedProposals->count() }})</h5>
                </div>
                <div class="card-body">
                    @if($rejectedProposals->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Entreprise</th>
                                    <th>Type d'activité</th>
                                    <th>Date souhaitée</th>
                                    <th>Lieu</th>
                                    <th>Date de refus</th>
                                    <th>Motif</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rejectedProposals as $proposal)
                                <tr>
                                    <td>{{ $proposal->id }}</td>
                                    <td>{{ $proposal->company->name }}</td>
                                    <td>{{ $proposal->eventType->title }}</td>
                                    <td>{{ $proposal->proposed_date->format('d/m/Y') }}</td>
                                    <td>{{ $proposal->location->name }} ({{ $proposal->location->city }})</td>
                                    <td>{{ $proposal->updated_at->format('d/m/Y H:i') }}</td>
                                    <td>{{ Str::limit($proposal->notes, 50) }}</td>
                                    <td>
                                        <a href="{{ route('admin.event_proposals.show', $proposal->id) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> Détails
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-info mb-0">
                        Aucune demande d'activité refusée.
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
