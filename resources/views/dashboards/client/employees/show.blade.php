@extends('layouts.admin')

@section('content')
<div class="container">
    <h1>Détails du collaborateur</h1>
    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4 font-weight-bold">Nom:</div>
                <div class="col-md-8">{{ $employee->last_name }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4 font-weight-bold">Prénom:</div>
                <div class="col-md-8">{{ $employee->first_name }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4 font-weight-bold">Email:</div>
                <div class="col-md-8">{{ $employee->email }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4 font-weight-bold">Téléphone:</div>
                <div class="col-md-8">{{ $employee->telephone ?: 'Non renseigné' }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4 font-weight-bold">Poste:</div>
                <div class="col-md-8">{{ $employee->position }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4 font-weight-bold">Département:</div>
                <div class="col-md-8">{{ $employee->departement ?: 'Non renseigné' }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4 font-weight-bold">Date de création du compte:</div>
                <div class="col-md-8">{{ $employee->date_creation_compte }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4 font-weight-bold">Dernière connexion:</div>
                <div class="col-md-8">{{ $employee->derniere_connexion ?: 'Jamais connecté' }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4 font-weight-bold">Préférences de langue:</div>
                <div class="col-md-8">
                    @switch($employee->preferences_langue)
                        @case('fr')
                            Français
                            @break
                        @case('en')
                            Anglais
                            @break
                        @case('es')
                            Espagnol
                            @break
                        @case('de')
                            Allemand
                            @break
                        @default
                            {{ $employee->preferences_langue }}
                    @endswitch
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4 font-weight-bold">ID Carte NFC:</div>
                <div class="col-md-8">{{ $employee->id_carte_nfc ?: 'Non attribuée' }}</div>
            </div>
            <div class="text-right mt-4">
                <a href="{{ route('employees.edit', $employee->id) }}" class="btn btn-warning">Modifier</a>
                <button onclick="window.history.back()" class="btn btn-secondary">Retour</button>
            </div>
        </div>
    </div>
</div>
@endsection
