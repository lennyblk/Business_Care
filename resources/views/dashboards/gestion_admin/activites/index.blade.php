@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Gestion des activités</h1>
        <div>
            <a href="{{ route('admin.activities.create') }}" class="btn btn-primary me-2">
                <i class="fas fa-plus"></i> Créer une activité
            </a>
            <a href="{{ route('admin.event_proposals.index') }}" class="btn btn-secondary">
                <i class="fas fa-list"></i> Voir les demandes d'activités
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white">
            <h4 class="mb-0">Activités assignées et acceptées</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Type d'activité</th>
                            <th>Entreprise</th>
                            <th>Date</th>
                            <th>Lieu</th>
                            <th>Statut</th>
                            <th>Prestataire</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($eventProposals as $proposal)
                            <tr>
                                <td>{{ $proposal->eventType->title }}</td>
                                <td>{{ $proposal->company->name }}</td>
                                <td>{{ \Carbon\Carbon::parse($proposal->proposed_date)->format('d/m/Y') }}</td>
                                <td>{{ $proposal->location->name }}</td>
                                <td>
                                    @if($proposal->status === 'Assigned')
                                        <span class="badge bg-info">Assignée</span>
                                    @else
                                        <span class="badge bg-success">Acceptée</span>
                                    @endif
                                </td>
                                <td>
                                    @if($proposal->providerAssignments->isNotEmpty())
                                        {{ $proposal->providerAssignments->first()->provider->first_name }}
                                        {{ $proposal->providerAssignments->first()->provider->last_name }}
                                    @else
                                        <span class="text-muted">Non assigné</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.event_proposals.show', $proposal->id) }}" 
                                       class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> Détails
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Aucune activité assignée ou acceptée</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
