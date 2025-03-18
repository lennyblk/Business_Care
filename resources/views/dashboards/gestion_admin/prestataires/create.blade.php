@extends('layouts.admin')

@section('content')
<div class="container">
    <h1>Créer un nouveau prestataire</h1>
    <form action="{{ route('admin.prestataires.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="last_name">Nom</label>
            <input type="text" name="last_name" id="last_name" class="form-control">
        </div>
        <div class="form-group">
            <label for="first_name">Prénom</label>
            <input type="text" name="first_name" id="first_name" class="form-control">
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" id="description" class="form-control" required></textarea>
        </div>
        <div class="form-group">
            <label for="domains">Domaines</label>
            <textarea name="domains" id="domains" class="form-control" required></textarea>
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
            <label for="password">Mot de passe</label>
            <input type="password" name="password" id="password" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="adresse">Adresse</label>
            <input type="text" name="adresse" id="adresse" class="form-control">
        </div>
        <div class="form-group">
            <label for="code_postal">Code Postal</label>
            <input type="text" name="code_postal" id="code_postal" class="form-control">
        </div>
        <div class="form-group">
            <label for="ville">Ville</label>
            <input type="text" name="ville" id="ville" class="form-control">
        </div>
        <div class="form-group">
            <label for="siret">SIRET</label>
            <input type="text" name="siret" id="siret" class="form-control">
        </div>
        <div class="form-group">
            <label for="iban">IBAN</label>
            <input type="text" name="iban" id="iban" class="form-control">
        </div>
        <div class="form-group">
            <label for="statut_prestataire">Statut</label>
            <select name="statut_prestataire" id="statut_prestataire" class="form-control" required>
                <option value="Candidat">Candidat</option>
                <option value="Validé">Validé</option>
                <option value="Inactif">Inactif</option>
            </select>
        </div>
        <div class="form-group">
            <label for="tarif_horaire">Tarif horaire</label>
            <input type="number" step="0.01" name="tarif_horaire" id="tarif_horaire" class="form-control">
        </div>
        <button type="submit" class="btn btn-primary">Créer</button>
        <button onclick="window.history.back()" type="button" class="btn btn-secondary">Retour</button>
    </form>
</div>
@endsection
