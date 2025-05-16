@extends('layouts.app')

@section('title', 'Mes demandes d\'activités')

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
                    <a href="{{ route('client.event_proposals.index') }}" class="list-group-item list-group-item-action active">Demande d'activités</a>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Mes demandes d'activités</h4>
                    <a href="{{ route('client.event_proposals.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nouvelle demande
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(!empty($eventProposals))
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Type d'activité</th>
                                        <th>Date souhaitée</th>
                                        <th>Lieu</th>
                                        <th>Statut</th>
                                        <th>Date de demande</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($eventProposals as $proposal)
                                        <tr>
                                            <td>{{ $proposal['id'] }}</td>
                                            <td>{{ $proposal['event_type']['title'] }}</td>
                                            <td>{{ \Carbon\Carbon::parse($proposal['proposed_date'])->format('d/m/Y') }}</td>
                                            <td>{{ $proposal['location']['name'] }}</td>
                                            <td>
                                                @if($proposal['status'] == 'Pending')
                                                    <span class="badge bg-warning text-dark">En attente</span>
                                                @elseif($proposal['status'] == 'Assigned')
                                                    <span class="badge bg-info">Assigné</span>
                                                @elseif($proposal['status'] == 'Accepted')
                                                    <span class="badge bg-success">Accepté</span>
                                                @elseif($proposal['status'] == 'Rejected')
                                                    <span class="badge bg-danger">Rejeté</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ $proposal['status'] }}</span>
                                                @endif
                                            </td>
                                            <td>{{ \Carbon\Carbon::parse($proposal['created_at'])->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <a href="{{ route('client.event_proposals.show', $proposal['id']) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i> Voir
                                                </a>
                                                @if($proposal['status'] == 'Pending')
                                                    <form method="POST" action="{{ route('client.event_proposals.destroy', $proposal['id']) }}" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir annuler cette demande?')">
                                                            <i class="fas fa-times"></i> Annuler
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            Vous n'avez pas encore soumis de demande d'activité.
                            <a href="{{ route('client.event_proposals.create') }}" class="alert-link">Créer une nouvelle demande</a>.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
