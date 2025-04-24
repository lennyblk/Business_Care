@extends('layouts.admin')

@section('content')
<div class="container">
    <h1>Ajouter un nouveau collaborateur</h1>
    <form action="{{ route('employees.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="first_name">Prénom</label>
            <input type="text" name="first_name" id="first_name" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="last_name">Nom</label>
            <input type="text" name="last_name" id="last_name" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="telephone">Téléphone</label>
            <input type="text" name="telephone" id="telephone" class="form-control">
        </div>
        <div class="form-group">
            <label for="position">Poste</label>
            <input type="text" name="position" id="position" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="departement">Département</label>
            <input type="text" name="departement" id="departement" class="form-control">
        </div>
        <div class="form-group">
            <label for="password">Mot de passe</label>
            <input type="password" name="password" id="password" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="password_confirmation">Confirmer le mot de passe</label>
            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="preferences_langue">Préférences de langue</label>
            <select name="preferences_langue" id="preferences_langue" class="form-control">
                <option value="fr" selected>Français</option>
                <option value="en">Anglais</option>
                <option value="es">Espagnol</option>
                <option value="de">Allemand</option>
            </select>
        </div>
        <div class="form-group">
            <label for="id_carte_nfc">ID Carte NFC (optionnel)</label>
            <input type="text" name="id_carte_nfc" id="id_carte_nfc" class="form-control">
        </div>
        <button type="submit" class="btn btn-primary">Créer</button>
        <button onclick="window.history.back()" type="button" class="btn btn-secondary">Retour</button>
    </form>
</div>
@endsection
