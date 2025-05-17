@extends('layouts.app')

@section('title', 'Détails de la demande d\'activité')

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
                    <h4 class="mb-0">Détails de la demande d'activité #{{ $eventProposal['id'] }}</h4>
                    <a href="{{ route('client.event_proposals.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Retour à la liste
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="border-bottom pb-2 mb-3">Informations générales</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <th style="width: 40%">ID de la demande:</th>
                                    <td>{{ $eventProposal['id'] }}</td>
                                </tr>
                                <tr>
                                    <th>Statut:</th>
                                    <td>
                                        @if($eventProposal['status'] == 'Pending')
                                            <span class="badge bg-warning text-dark">En attente</span>
                                        @elseif($eventProposal['status'] == 'Assigned')
                                            <span class="badge bg-info">Assigné à un prestataire</span>
                                        @elseif($eventProposal['status'] == 'Accepted')
                                            <span class="badge bg-success">Accepté</span>
                                        @elseif($eventProposal['status'] == 'Rejected')
                                            <span class="badge bg-danger">Rejeté</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $eventProposal['status'] }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Date de soumission:</th>
                                    <td>{{ \Carbon\Carbon::parse($eventProposal['created_at'])->format('d/m/Y H:i') }}</td>
                                </tr>
                                @if(\Carbon\Carbon::parse($eventProposal['updated_at'])->diffInSeconds(\Carbon\Carbon::parse($eventProposal['created_at'])) > 1)
                                <tr>
                                    <th>Dernière mise à jour:</th>
                                    <td>{{ \Carbon\Carbon::parse($eventProposal['updated_at'])->format('d/m/Y H:i') }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5 class="border-bottom pb-2 mb-3">Détails de l'activité</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <th style="width: 40%">Type d'activité:</th>
                                    <td>{{ $eventProposal['event_type']['title'] }}</td>
                                </tr>
                                <tr>
                                    <th>Date souhaitée:</th>
                                    <td>{{ \Carbon\Carbon::parse($eventProposal['proposed_date'])->format('d/m/Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Lieu:</th>
                                    <td>{{ $eventProposal['location']['name'] }} ({{ $eventProposal['location']['city'] }})</td>
                                </tr>
                                <th>Durée:</th>
                                <td>
                                    @if($eventProposal['duration'] >= 60)
                                        {{ floor($eventProposal['duration'] / 60) }} h
                                        @if($eventProposal['duration'] % 60 > 0)
                                            {{ $eventProposal['duration'] % 60 }} min
                                        @endif
                                    @else
                                        {{ $eventProposal['duration'] }} min
                                    @endif
                                </td>
                            </tr>
                            <tr>
                            </table>
                        </div>
                    </div>

                    @if($eventProposal['notes'])
                    <div class="row mt-3">
                        <div class="col-12">
                            <h5 class="border-bottom pb-2 mb-3">Remarques</h5>
                            <div class="p-3 bg-light rounded">
                                {{ $eventProposal['notes'] }}
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($eventProposal['status'] == 'Accepted')
                        @if(isset($eventProposal['event']))
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="alert alert-success">
                                    <h5 class="alert-heading">Activité confirmée!</h5>
                                    <p>Votre activité <strong>{{ $eventProposal['event']['name'] }}</strong> a été confirmée et planifiée pour le <strong>{{ \Carbon\Carbon::parse($eventProposal['event']['date'])->format('d/m/Y') }}</strong>.</p>
                                    <hr>
                                    <p class="mb-0">Vos employés peuvent maintenant s'y inscrire via leur espace personnel.</p>
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <h5 class="alert-heading">Activité acceptée</h5>
                                    <p>Votre demande d'activité a été acceptée et la création de l'événement est en cours.</p>
                                </div>
                            </div>
                        </div>
                        @endif
                    @elseif($eventProposal['status'] == 'Assigned')
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <h5 class="alert-heading">Demande en cours de traitement</h5>
                                    <p>Votre demande a été assignée à un prestataire et est en attente de confirmation.</p>
                                    <p>Vous serez notifié dès que le prestataire aura confirmé sa disponibilité.</p>
                                </div>
                            </div>
                        </div>
                    @elseif($eventProposal['status'] == 'Pending')
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="alert alert-warning">
                                    <h5 class="alert-heading">Demande en attente</h5>
                                    <p>Votre demande est en cours d'examen par notre équipe. Un prestataire vous sera attribué très prochainement.</p>
                                </div>
                            </div>
                        </div>
                    @elseif($eventProposal['status'] == 'Rejected')
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="alert alert-danger">
                                    <h5 class="alert-heading">Demande rejetée</h5>
                                    <p>Nous sommes désolés, mais votre demande d'activité a été rejetée. Veuillez nous contacter pour plus d'informations.</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
