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
        <div class="form-group">
            <label for="statut_prestataire">Statut</label>
            <select name="statut_prestataire" id="statut_prestataire" class="form-control" required>
                <option value="Candidat" {{ ($prestataire['statut_prestataire'] ?? '') == 'Candidat' ? 'selected' : '' }}>Candidat</option>
                <option value="Validé" {{ ($prestataire['statut_prestataire'] ?? '') == 'Validé' ? 'selected' : '' }}>Validé</option>
                <option value="Inactif" {{ ($prestataire['statut_prestataire'] ?? '') == 'Inactif' ? 'selected' : '' }}>Inactif</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Mettre à jour</button>
    </form>
</div>
@endsection
