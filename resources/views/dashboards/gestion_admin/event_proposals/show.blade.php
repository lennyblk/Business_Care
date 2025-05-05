@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Détails de la demande d'activité</h2>
            <a href="{{ route('admin.event_proposals.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Retour à la liste
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="m-0">Informations sur la demande</h5>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">Entreprise :</dt>
                        <dd class="col-sm-8">{{ $eventProposal->company->name }}</dd>

                        <dt class="col-sm-4">Activité demandée :</dt>
                        <dd class="col-sm-8">{{ $eventProposal->eventType->title }}</dd>

                        <dt class="col-sm-4">Date souhaitée :</dt>
                        <dd class="col-sm-8">{{ $eventProposal->proposed_date->format('d/m/Y') }}</dd>

                        <dt class="col-sm-4">Site :</dt>
                        <dd class="col-sm-8">{{ $eventProposal->location->name }} ({{ $eventProposal->location->city }})</dd>

                        <dt class="col-sm-4">Statut :</dt>
                        <dd class="col-sm-8">
                            <span class="badge {{ $eventProposal->status == 'Pending' ? 'bg-warning' : ($eventProposal->status == 'Assigned' ? 'bg-info' : 'bg-success') }}">
                                {{ $eventProposal->status }}
                            </span>
                        </dd>

                        <dt class="col-sm-4">Date de demande :</dt>
                        <dd class="col-sm-8">{{ $eventProposal->created_at->format('d/m/Y H:i') }}</dd>

                        @if($eventProposal->notes)
                        <dt class="col-sm-4">Remarques :</dt>
                        <dd class="col-sm-8">{{ $eventProposal->notes }}</dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="m-0">Prestataires recommandés</h5>
                </div>
                <div class="card-body">
                    @if($eventProposal->status == 'Pending')
                        @if($recommendations->count() > 0)
                            @foreach($recommendations as $provider)
                            <div class="provider-card mb-3 p-3 border rounded">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h5>{{ $provider->first_name }} {{ $provider->last_name }}</h5>
                                        <p>{{ $provider->domains }}</p>
                                        <p>
                                            <i class="fas fa-star text-warning"></i> {{ $provider->rating }}
                                            ({{ $provider->nombre_evaluations }} évaluations)
                                        </p>
                                        <p><i class="fas fa-map-marker-alt"></i> {{ $provider->ville }}</p>
                                        <p><i class="fas fa-euro-sign"></i> {{ $provider->tarif_horaire }}€/heure</p>
                                    </div>
                                    <div class="col-md-4 text-end d-flex flex-column justify-content-center">
                                        <button type="button" class="btn btn-primary mb-2"
                                                data-bs-toggle="modal"
                                                data-bs-target="#assignModal{{ $provider->id }}">
                                            <i class="fas fa-user-check"></i> Assigner
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal d'assignation -->
                            <div class="modal fade" id="assignModal{{ $provider->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('admin.event_proposals.assign', $eventProposal->id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="provider_id" value="{{ $provider->id }}">

                                            <div class="modal-header">
                                                <h5 class="modal-title">Assigner {{ $provider->first_name }} {{ $provider->last_name }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Vous êtes sur le point d'assigner ce prestataire à l'activité demandée.</p>

                                                <div class="form-group mb-3">
                                                    <label for="payment_amount">Montant proposé (€)</label>
                                                    <input type="number" class="form-control" id="payment_amount" name="payment_amount"
                                                           value="{{ $provider->tarif_horaire * 2 }}" min="0" step="0.01" required>
                                                    <small class="form-text text-muted">
                                                        Montant basé sur le tarif horaire du prestataire ({{ $provider->tarif_horaire }}€/h)
                                                    </small>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                <button type="submit" class="btn btn-primary">Confirmer l'assignation</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        @else
                        <div class="alert alert-warning">
                            Aucun prestataire correspondant trouvé. Veuillez en rechercher un manuellement.
                        </div>
                        @endif
                    @else
                        @if($eventProposal->providerAssignments->count() > 0)
                            @foreach($eventProposal->providerAssignments as $assignment)
                            <div class="assignment-card mb-3 p-3 border rounded">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h5>{{ $assignment->provider->first_name }} {{ $assignment->provider->last_name }}</h5>
                                        <p>Statut:
                                            <span class="badge {{ $assignment->status == 'Proposed' ? 'bg-warning' : ($assignment->status == 'Accepted' ? 'bg-success' : 'bg-danger') }}">
                                                {{ $assignment->status }}
                                            </span>
                                        </p>
                                        <p>Montant proposé: {{ $assignment->payment_amount }}€</p>
                                        <p>Proposé le: {{ $assignment->proposed_at->format('d/m/Y H:i') }}</p>
                                        @if($assignment->response_at)
                                        <p>Réponse le: {{ $assignment->response_at->format('d/m/Y H:i') }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        @else
                        <div class="alert alert-info">
                            Aucun prestataire n'a encore été assigné à cette activité.
                        </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
