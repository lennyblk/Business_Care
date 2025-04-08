@extends('layouts.admin')

@section('content')
<div class="container">
    <h1>Créer un nouveau salarié</h1>
    <form action="{{ route('admin.salaries.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="company_id">Entreprise</label>
            <select name="company_id" id="company_id" class="form-control">
                <option value="">Sélectionnez une entreprise</option>
                @foreach($companies as $company)
                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                @endforeach
            </select>
        </div>
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
            <label for="preferences_langue">Préférences de langue</label>
            <input type="text" name="preferences_langue" id="preferences_langue" class="form-control" value="fr">
        </div>
        <div class="form-group">
            <label for="id_carte_nfc">ID Carte NFC</label>
            <input type="text" name="id_carte_nfc" id="id_carte_nfc" class="form-control">
        </div>
        <button type="submit" class="btn btn-primary">Créer</button>
        <button onclick="window.history.back()" type="button" class="btn btn-secondary">Retour</button>
    </form>
</div>
@endsection
