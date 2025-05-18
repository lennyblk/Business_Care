@extends('layouts.admin')
@section('content')
<div class="container">
    <h1>Modifier le prestataire</h1>
    <form action="{{ route('admin.prestataires.update', $prestataire['id']) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="last_name">Nom</label>
            <input type="text" name="last_name" id="last_name" class="form-control" value="{{ $prestataire['last_name'] ?? '' }}" required>
        </div>
        <div class="form-group">
            <label for="first_name">Prénom</label>
            <input type="text" name="first_name" id="first_name" class="form-control" value="{{ $prestataire['first_name'] ?? '' }}" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" class="form-control" value="{{ $prestataire['email'] ?? '' }}" required>
        </div>
        <!-- Champs manquants obligatoires -->
        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" id="description" class="form-control" required>{{ $prestataire['description'] ?? '' }}</textarea>
        </div>
        <div class="form-group">
            <label for="domains">Domaines d'expertise</label>
            <input type="text" name="domains" id="domains" class="form-control" value="{{ $prestataire['domains'] ?? '' }}" required>
        </div>
        <!-- Autres champs facultatifs -->
        <div class="form-group">
            <label for="telephone">Téléphone</label>
            <input type="text" name="telephone" id="telephone" class="form-control" value="{{ $prestataire['telephone'] ?? '' }}">
        </div>
        <div class="form-group">
            <label for="adresse">Adresse</label>
            <input type="text" name="adresse" id="adresse" class="form-control" value="{{ $prestataire['adresse'] ?? '' }}">
        </div>
        <div class="form-group">
            <label for="code_postal">Code postal</label>
            <input type="text" name="code_postal" id="code_postal" class="form-control" value="{{ $prestataire['code_postal'] ?? '' }}">
        </div>
        <div class="form-group">
            <label for="ville">Ville</label>
            <input type="text" name="ville" id="ville" class="form-control" value="{{ $prestataire['ville'] ?? '' }}">
        </div>
        <div class="form-group">
            <label for="statut_prestataire">Statut</label>
            <select name="statut_prestataire" id="statut_prestataire" class="form-control" required>
                <option value="Candidat" {{ ($prestataire['statut_prestataire'] ?? '') == 'Candidat' ? 'selected' : '' }}>Candidat</option>
                <option value="Validé" {{ ($prestataire['statut_prestataire'] ?? '') == 'Validé' ? 'selected' : '' }}>Validé</option>
                <option value="Inactif" {{ ($prestataire['statut_prestataire'] ?? '') == 'Inactif' ? 'selected' : '' }}>Inactif</option>
            </select>
        </div>
        <div class="form-group">
            <label for="tarif_horaire">Tarif horaire</label>
            <input type="number" step="0.01" name="tarif_horaire" id="tarif_horaire" class="form-control" value="{{ $prestataire['tarif_horaire'] ?? '' }}">
        </div>
        <button type="submit" class="btn btn-primary">Mettre à jour</button>
        <button type="button" class="btn btn-secondary" onclick="window.history.back()">Annuler</button>
    </form>
</div>
@endsection
