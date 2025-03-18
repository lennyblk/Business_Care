@extends('layouts.admin')

@section('content')
<div class="container">
    <h1>Détails du prestataire</h1>
    <div class="card mb-4">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4 font-weight-bold">Nom:</div>
                <div class="col-md-8">{{ $prestataire->last_name }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4 font-weight-bold">Prénom:</div>
                <div class="col-md-8">{{ $prestataire->first_name }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4 font-weight-bold">Email:</div>
                <div class="col-md-8">{{ $prestataire->email }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4 font-weight-bold">Téléphone:</div>
                <div class="col-md-8">{{ $prestataire->telephone }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4 font-weight-bold">Description:</div>
                <div class="col-md-8">{{ $prestataire->description }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4 font-weight-bold">Domaines:</div>
                <div class="col-md-8">{{ $prestataire->domains }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4 font-weight-bold">Statut:</div>
                <div class="col-md-8">{{ $prestataire->statut_prestataire }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4 font-weight-bold">Tarif horaire:</div>
                <div class="col-md-8">{{ $prestataire->tarif_horaire }}</div>
            </div>
        </div>
    </div>

    <h2>Disponibilités</h2>
    <div class="card mb-4">
        <div class="card-body">
            @if($disponibilites->isEmpty())
                <p>Aucune disponibilité trouvée.</p>
            @else
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Heure de début</th>
                            <th>Heure de fin</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($disponibilites as $disponibilite)
                            <tr>
                                <td>{{ $disponibilite->date }}</td>
                                <td>{{ $disponibilite->start_time }}</td>
                                <td>{{ $disponibilite->end_time }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    <h2>Évaluations</h2>
    <div class="card mb-4">
        <div class="card-body">
            @if($evaluations->isEmpty())
                <p>Aucune évaluation trouvée.</p>
            @else
                <table class="table table-bordered">
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
                                <td>{{ $evaluation->date }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    <h2>Factures</h2>
    <div class="card mb-4">
        <div class="card-body">
            @if($factures->isEmpty())
                <p>Aucune facture trouvée.</p>
            @else
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Montant</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($factures as $facture)
                            <tr>
                                <td>{{ $facture->id }}</td>
                                <td>{{ $facture->date }}</td>
                                <td>{{ $facture->amount }}</td>
                                <td>{{ $facture->status }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    <div class="text-right">
        <button onclick="window.history.back()" class="btn btn-secondary">Retour</button>
        <button onclick="window.location='{{ route('admin.prestataires.index') }}'" class="btn btn-primary">Retour à la liste des prestataires</button>
    </div>
</div>
@endsection
