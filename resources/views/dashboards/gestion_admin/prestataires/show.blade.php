@extends('layouts.admin')

@section('content')
<div class="container">
    <h1>Détails du prestataire</h1>
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">{{ $prestataire->first_name }} {{ $prestataire->last_name }}</h5>
            <p class="card-text"><strong>Email :</strong> {{ $prestataire->email }}</p>
            <p class="card-text"><strong>Statut :</strong> {{ $prestataire->statut_prestataire }}</p>
            <p class="card-text"><strong>Date de validation :</strong> {{ $prestataire->date_validation }}</p>
        </div>
    </div>

    <h2>Disponibilités</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Heure de début</th>
                <th>Heure de fin</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            @foreach($disponibilites as $disponibilite)
            <tr>
                <td>{{ $disponibilite->date_available }}</td>
                <td>{{ $disponibilite->start_time }}</td>
                <td>{{ $disponibilite->end_time }}</td>
                <td>{{ $disponibilite->status }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Évaluations</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Note</th>
                <th>Commentaire</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($evaluations as $evaluation)
            <tr>
                <td>{{ $evaluation->rating }}</td>
                <td>{{ $evaluation->comment }}</td>
                <td>{{ $evaluation->evaluation_date }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Factures</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Mois</th>
                <th>Année</th>
                <th>Montant</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            @foreach($factures as $facture)
            <tr>
                <td>{{ $facture->month }}</td>
                <td>{{ $facture->year }}</td>
                <td>{{ $facture->total_amount }}</td>
                <td>{{ $facture->payment_status }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
