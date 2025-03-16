@extends('layouts.admin')

@section('content')
<div class="container">
    <h1>Créer un nouveau prestataire</h1>
    <form action="{{ route('admin.prestataires.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="last_name">Nom</label>
            <input type="text" name="last_name" id="last_name" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="first_name">Prénom</label>
            <input type="text" name="first_name" id="first_name" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="password">Mot de passe</label>
            <input type="password" name="password" id="password" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="statut_prestataire">Statut</label>
            <select name="statut_prestataire" id="statut_prestataire" class="form-control" required>
                <option value="Candidat">Candidat</option>
                <option value="Validé">Validé</option>
                <option value="Inactif">Inactif</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Créer</button>
    </form>
</div>
@endsection
