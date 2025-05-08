@extends('layouts.admin')

@section('title', 'Détails de l\'assignation')

@section('content')
<div class="container">
    <div class="mb-3">
        <a href="{{ route('provider.assignments.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Retour aux assignations
        </a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h1 class="h4 mb-0">Détails de l'assignation</h1>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h2 class="h5 mb-3">Informations générales</h2>
                    <p><strong>Entreprise:</strong> {{ $assignment->eventProposal->company->name }}</p>
                    <p><strong>Type d'activité:</strong> {{ $assignment->eventProposal->eventType->title }}</p>
                    <p><strong>Description:</strong> {{ $assignment->eventProposal->eventType->description }}</p>
                    <p><strong>Date proposée:</strong> {{ date('d/m/Y', strtotime($assignment->eventProposal->proposed_date)) }}</p>
                    <p><strong>Statut:</strong>
                        @if($assignment->status == 'Proposed')
                            <span class="badge bg-warning">En attente</span>
                        @elseif($assignment->status == 'Accepted')
                            <span class="badge bg-success">Acceptée</span>
                        @elseif($assignment->status == 'Rejected')
                            <span class="badge bg-danger">Refusée</span>
                        @endif
                    </p>
                    <p><strong>Montant:</strong> {{ number_format($assignment->payment_amount, 2, ',', ' ') }} €</p>
                </div>
                <div class="col-md-6">
                    <h2 class="h5 mb-3">Lieu</h2>
                    <p><strong>Nom:</strong> {{ $assignment->eventProposal->location->name }}</p>
                    <p><strong>Adresse:</strong> {{ $assignment->eventProposal->location->address }}</p>
                    <p><strong>Code postal:</strong> {{ $assignment->eventProposal->location->postal_code }}</p>
                    <p><strong>Ville:</strong> {{ $assignment->eventProposal->location->city }}</p>
                    <p><strong>Pays:</strong> {{ $assignment->eventProposal->location->country }}</p>
                </div>
            </div>

            @if($assignment->status == 'Proposed')
                <div class="mt-4">
                    <h2 class="h5 mb-3">Actions</h2>
                    <div class="d-flex gap-2">
                        <form method="POST" action="{{ route('provider.assignments.accept', $assignment->id) }}">
                            @csrf
                            <button type="submit" class="btn btn-success" onclick="return confirm('Êtes-vous sûr de vouloir accepter cette activité?')">
                                <i class="fas fa-check"></i> Accepter cette activité
                            </button>
                        </form>
                        <form method="POST" action="{{ route('provider.assignments.reject', $assignment->id) }}">
                            @csrf
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir refuser cette activité?')">
                                <i class="fas fa-times"></i> Refuser cette activité
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
