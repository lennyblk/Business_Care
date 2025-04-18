@extends('layouts.admin')

@section('content')
<div class="container">
    <h1 class="mb-4">Détails de l'entreprise</h1>
    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4 font-weight-bold">Nom de l'entreprise:</div>
                <div class="col-md-8">{{ $company->name }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4 font-weight-bold">Adresse:</div>
                <div class="col-md-8">{{ $company->address }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4 font-weight-bold">Code Postal:</div>
                <div class="col-md-8">{{ $company->code_postal }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4 font-weight-bold">Ville:</div>
                <div class="col-md-8">{{ $company->ville }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4 font-weight-bold">Pays:</div>
                <div class="col-md-8">{{ $company->pays }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4 font-weight-bold">Téléphone:</div>
                <div class="col-md-8">{{ $company->telephone }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4 font-weight-bold">Date de Création:</div>
                <div class="col-md-8">{{ $company->creation_date }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4 font-weight-bold">Email:</div>
                <div class="col-md-8">{{ $company->email }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4 font-weight-bold">SIRET:</div>
                <div class="col-md-8">{{ $company->siret }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4 font-weight-bold">Formule d'abonnement:</div>
                <div class="col-md-8">{{ $company->formule_abonnement }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4 font-weight-bold">Statut du compte:</div>
                <div class="col-md-8">{{ $company->statut_compte }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4 font-weight-bold">Date de début du contrat:</div>
                <div class="col-md-8">{{ $company->date_debut_contrat }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4 font-weight-bold">Date de fin du contrat:</div>
                <div class="col-md-8">{{ $company->date_fin_contrat }}</div>
            </div>
            <div class="text-right">
                <button onclick="window.history.back()" class="btn btn-secondary">Retour</button>
                <button onclick="window.location='{{ route('admin.company.contracts', $company->id) }}'" class="btn btn-primary">Voir les contrats</button>
            </div>
        </div>
    </div>

    <h2 class="mt-5">Salariés de l'entreprise {{ $company->name }}</h2>
    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Email</th>
                <th>Poste</th>
            </tr>
        </thead>
        <tbody>
            @forelse($employees as $employee)
            <tr>
                <td>{{ $employee->last_name }}</td>
                <td>{{ $employee->first_name }}</td>
                <td>{{ $employee->email }}</td>
                <td>{{ $employee->position }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center">Aucun salarié trouvé pour cette entreprise.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
