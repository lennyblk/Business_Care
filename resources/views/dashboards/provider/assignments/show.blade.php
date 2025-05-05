@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Détails de la proposition d'activité</h2>
            <a href="{{ route('provider.assignments.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Retour à la liste
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="m-0">Informations sur l'activité proposée</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-4">
                        <h5 class="alert-heading">Business Care vous propose d'animer une activité</h5>
                        <p>Veuillez examiner les détails ci-dessous et donner votre réponse.</p>
                    </div>

                    <dl class="row">
                        <dt class="col-sm-4">Type d'activité :</dt>
                        <dd class="col-sm-8">{{ $assignment->eventProposal->eventType->title }}</dd>

                        <dt class="col-sm-4">Description :</dt>
                        <dd class="col-sm-8">{{ $assignment->eventProposal->eventType->description }}</dd>

                        <dt class="col-sm-4">Date prévue :</dt>
                        <dd class="col-sm-8">{{ $assignment->eventProposal->proposed_date->format('d/m/Y') }}</dd>

                        <dt class="col-sm-4">Lieu :</dt>
                        <dd class="col-sm-8">
                            {{ $assignment->eventProposal->location->name }}<br>
                            {{ $assignment->eventProposal->location->address }}<br>
                            {{ $assignment->eventProposal->location->postal_code }} {{ $assignment->eventProposal->location->city }}
                        </dd>

                        <dt class="col-sm-4">Entreprise cliente :</dt>
                        <dd class="col-sm-8">{{ $assignment->eventProposal->company->name }}</dd>

                        <dt class="col-sm-4">Montant proposé :</dt>
                        <dd class="col-sm-8"><strong>{{ $assignment->payment_amount }}€</strong></dd>

                        <dt class="col-sm-4">Statut actuel :</dt>
                        <dd class="col-sm-8">
                            <span class="badge {{ $assignment->status == 'Proposed' ? 'bg-warning' : ($assignment->status == 'Accepted' ? 'bg-success' : 'bg-danger') }}">
                                {{ $assignment->status }}
                            </span>
                        </dd>

                        <dt class="col-sm-4">Date de proposition :</dt>
                        <dd class="col-sm-8">{{ $assignment->proposed_at->format('d/m/Y) }}</dd>
                        <dd class="col-sm-8">{{ $assignment->proposed_at->format('d/m/Y H:i') }}</dd>

                        @if($assignment->eventProposal->notes)
                        <dt class="col-sm-4">Remarques :</dt>
                        <dd class="col-sm-8">{{ $assignment->eventProposal->notes }}</dd>
                        @endif
                    </dl>

                    @if($assignment->status == 'Proposed')
                    <div class="row mt-4">
                        <div class="col-md-12 text-center">
                            <p><strong>Souhaitez-vous accepter cette proposition d'activité ?</strong></p>
                            <div class="d-flex justify-content-center gap-3 mt-3">
                                <form action="{{ route('provider.assignments.accept', $assignment->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-lg">
                                        <i class="fas fa-check-circle"></i> Accepter
                                    </button>
                                </form>

                                <form action="{{ route('provider.assignments.reject', $assignment->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-lg">
                                        <i class="fas fa-times-circle"></i> Refuser
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @elseif($assignment->status == 'Accepted')
                    <div class="alert alert-success mt-4">
                        <h5 class="alert-heading">Activité confirmée !</h5>
                        <p>Vous avez accepté cette activité le {{ $assignment->response_at->format('d/m/Y') }}.</p>
                        <p>Un événement a été créé et les employés de l'entreprise peuvent maintenant s'y inscrire.</p>
                        <hr>
                        <p class="mb-0">N'oubliez pas de consulter votre calendrier pour voir toutes vos activités planifiées.</p>
                    </div>
                    @else
                    <div class="alert alert-secondary mt-4">
                        <h5 class="alert-heading">Proposition refusée</h5>
                        <p>Vous avez refusé cette activité le {{ $assignment->response_at->format('d/m/Y') }}.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
