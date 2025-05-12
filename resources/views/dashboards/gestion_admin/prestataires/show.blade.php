@extends('layouts.admin')

@section('content')
<div class="container">
    <h1>Détails du prestataire</h1>
    <div class="card mb-4">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4 font-weight-bold">Nom:</div>
                <div class="col-md-8">{{ $prestataire['last_name'] ?? 'N/A' }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4 font-weight-bold">Prénom:</div>
                <div class="col-md-8">{{ $prestataire['first_name'] ?? 'N/A' }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4 font-weight-bold">Email:</div>
                <div class="col-md-8">{{ $prestataire['email'] ?? 'N/A' }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4 font-weight-bold">Téléphone:</div>
                <div class="col-md-8">{{ $prestataire['telephone'] ?? 'N/A' }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4 font-weight-bold">Description:</div>
                <div class="col-md-8">{{ $prestataire['description'] ?? 'N/A' }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4 font-weight-bold">Domaines:</div>
                <div class="col-md-8">{{ $prestataire['domains'] ?? 'N/A' }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4 font-weight-bold">Statut:</div>
                <div class="col-md-8">{{ $prestataire['statut_prestataire'] ?? 'N/A' }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4 font-weight-bold">Tarif horaire:</div>
                <div class="col-md-8">{{ $prestataire['tarif_horaire'] ?? 'N/A' }}</div>
            </div>
        </div>
    </div>

    <h2>Disponibilités</h2>
    <div class="card mb-4">
        <div class="card-body">
            @if(empty($disponibilites) || count($disponibilites) == 0)
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
                                <td>{{ $disponibilite['date'] ?? 'N/A' }}</td>
                                <td>{{ $disponibilite['start_time'] ?? 'N/A' }}</td>
                                <td>{{ $disponibilite['end_time'] ?? 'N/A' }}</td>
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
            @if(empty($evaluations) || count($evaluations) == 0)
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
                                <td>{{ $evaluation['rating'] ?? 'N/A' }}</td>
                                <td>{{ $evaluation['comment'] ?? 'N/A' }}</td>
                                <td>{{ $evaluation['date'] ?? 'N/A' }}</td>
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
            @if(empty($factures) || count($factures) == 0)
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
                                <td>{{ $facture['id'] ?? 'N/A' }}</td>
                                <td>{{ $facture['date'] ?? 'N/A' }}</td>
                                <td>{{ $facture['amount'] ?? 'N/A' }}</td>
                                <td>{{ $facture['status'] ?? 'N/A' }}</td>
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
