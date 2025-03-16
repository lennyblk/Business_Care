@extends('layouts.admin')

@section('content')
<div class="container">
    <h1>Modifier le salarié</h1>
    <form action="{{ route('admin.salaries.update', $employee->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="first_name">Prénom</label>
            <input type="text" name="first_name" id="first_name" class="form-control" value="{{ $employee->first_name }}" required>
        </div>
        <div class="form-group">
            <label for="last_name">Nom</label>
            <input type="text" name="last_name" id="last_name" class="form-control" value="{{ $employee->last_name }}" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" class="form-control" value="{{ $employee->email }}" required>
        </div>
        <div class="form-group">
            <label for="telephone">Téléphone</label>
            <input type="text" name="telephone" id="telephone" class="form-control" value="{{ $employee->telephone }}">
        </div>
        <div class="form-group">
            <label for="position">Poste</label>
            <input type="text" name="position" id="position" class="form-control" value="{{ $employee->position }}" required>
        </div>
        <div class="form-group">
            <label for="departement">Département</label>
            <input type="text" name="departement" id="departement" class="form-control" value="{{ $employee->departement }}">
        </div>
        <div class="form-group">
            <label for="password">Mot de passe</label>
            <input type="password" name="password" id="password" class="form-control">
        </div>
        <div class="form-group">
            <label for="preferences_langue">Préférences de langue</label>
            <input type="text" name="preferences_langue" id="preferences_langue" class="form-control" value="{{ $employee->preferences_langue }}">
        </div>
        <div class="form-group">
            <label for="id_carte_nfc">ID Carte NFC</label>
            <input type="text" name="id_carte_nfc" id="id_carte_nfc" class="form-control" value="{{ $employee->id_carte_nfc }}">
        </div>
        <button type="submit" class="btn btn-primary">Mettre à jour</button>
        <button onclick="window.history.back()" type="button" class="btn btn-secondary">Retour</button>
    </form>
</div>
@endsection
