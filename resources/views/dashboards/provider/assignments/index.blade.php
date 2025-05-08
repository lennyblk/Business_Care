@extends('layouts.admin')

@section('title', 'Mes Assignations')

@section('content')
<div class="container">
    <h1 class="mb-4">Mes Assignations</h1>

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

    <div class="card mb-4">
        <div class="card-header bg-warning">
            <h2 class="h5 mb-0">Assignations en attente</h2>
        </div>
        <div class="card-body">
            @if(count($pendingAssignments) > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Entreprise</th>
                                <th>Type d'activité</th>
                                <th>Date proposée</th>
                                <th>Lieu</th>
                                <th>Montant</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingAssignments as $assignment)
                                <tr>
                                    <td>{{ $assignment->eventProposal->company->name }}</td>
                                    <td>{{ $assignment->eventProposal->eventType->title }}</td>
                                    <td>{{ date('d/m/Y', strtotime($assignment->eventProposal->proposed_date)) }}</td>
                                    <td>{{ $assignment->eventProposal->location->name }}</td>
                                    <td>{{ number_format($assignment->payment_amount, 2, ',', ' ') }} €</td>
                                    <td>
                                        <a href="{{ route('provider.assignments.show', $assignment->id) }}" class="btn btn-sm btn-info">Détails</a>
                                        <form method="POST" action="{{ route('provider.assignments.accept', $assignment->id) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Êtes-vous sûr de vouloir accepter cette activité?')">Accepter</button>
                                        </form>
                                        <form method="POST" action="{{ route('provider.assignments.reject', $assignment->id) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir refuser cette activité?')">Refuser</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p>Aucune assignation en attente.</p>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-success text-white">
            <h2 class="h5 mb-0">Assignations acceptées</h2>
        </div>
        <div class="card-body">
            @if(count($acceptedAssignments) > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Entreprise</th>
                                <th>Type d'activité</th>
                                <th>Date</th>
                                <th>Lieu</th>
                                <th>Montant</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($acceptedAssignments as $assignment)
                                <tr>
                                    <td>{{ $assignment->eventProposal->company->name }}</td>
                                    <td>{{ $assignment->eventProposal->eventType->title }}</td>
                                    <td>{{ date('d/m/Y', strtotime($assignment->eventProposal->proposed_date)) }}</td>
                                    <td>{{ $assignment->eventProposal->location->name }}</td>
                                    <td>{{ number_format($assignment->payment_amount, 2, ',', ' ') }} €</td>
                                    <td>
                                        <a href="{{ route('provider.assignments.show', $assignment->id) }}" class="btn btn-sm btn-info">Détails</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p>Aucune assignation acceptée.</p>
            @endif
        </div>
    </div>
</div>
@endsection
