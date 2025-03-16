@extends('layouts.admin')

@section('content')
<div class="container">
    <h1 class="mb-4">Contrats de l'entreprise {{ $company->name }}</h1>
    <div class="card">
        <div class="card-body">
            @if($contrats->isEmpty())
                <p>Aucun contrat trouvé pour cette entreprise.</p>
            @else
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Date de début</th>
                            <th>Date de fin</th>
                            <th>Services</th>
                            <th>Montant</th>
                            <th>Méthode de paiement</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($contrats as $contrat)
                            <tr>
                                <td>{{ $contrat->id }}</td>
                                <td>{{ $contrat->start_date }}</td>
                                <td>{{ $contrat->end_date }}</td>
                                <td>{{ $contrat->services }}</td>
                                <td>{{ $contrat->amount }}</td>
                                <td>{{ $contrat->payment_method }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
            <div class="text-right">
                <button onclick="window.history.back()" class="btn btn-secondary">Retour</button>
            </div>
        </div>
    </div>
</div>
@endsection
