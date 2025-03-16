@extends('layouts.admin')

@section('content')
<div class="container">
    <h1>Créer une nouvelle entreprise</h1>
    <form action="{{ route('admin.company.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="name">Nom de l'entreprise</label>
            <input type="text" name="name" id="name" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="address">Adresse</label>
            <input type="text" name="address" id="address" class="form-control" required>
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
            <label for="pays">Pays</label>
            <input type="text" name="pays" id="pays" class="form-control" value="France">
        </div>
        <div class="form-group">
            <label for="phone">Téléphone</label>
            <input type="text" name="phone" id="phone" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="creation_date">Date de Création</label>
            <input type="date" name="creation_date" id="creation_date" class="form-control" required>
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
            <label for="siret">SIRET</label>
            <input type="text" name="siret" id="siret" class="form-control">
        </div>
        <div class="form-group">
            <label for="formule_abonnement">Formule d'abonnement</label>
            <select name="formule_abonnement" id="formule_abonnement" class="form-control" required>
                <option value="Starter">Starter</option>
                <option value="Basic">Basic</option>
                <option value="Premium">Premium</option>
            </select>
        </div>
        <div class="form-group">
            <label for="statut_compte">Statut du compte</label>
            <select name="statut_compte" id="statut_compte" class="form-control" required>
                <option value="Actif">Actif</option>
                <option value="Inactif">Inactif</option>
            </select>
        </div>
        <div class="form-group">
            <label for="date_debut_contrat">Date de début du contrat</label>
            <input type="date" name="date_debut_contrat" id="date_debut_contrat" class="form-control">
        </div>
        <div class="form-group">
            <label for="date_fin_contrat">Date de fin du contrat</label>
            <input type="date" name="date_fin_contrat" id="date_fin_contrat" class="form-control">
        </div>
        <div class="form-group">
            <label for="mode_paiement_prefere">Mode de paiement préféré</label>
            <input type="text" name="mode_paiement_prefere" id="mode_paiement_prefere" class="form-control">
        </div>
        <div class="form-group">
            <label for="employee_count">Nombre d'employés</label>
            <input type="number" name="employee_count" id="employee_count" class="form-control" value="0">
        </div>
        <button type="submit" class="btn btn-primary">Créer</button>
        <button onclick="window.history.back()" type="button" class="btn btn-secondary">Retour</button>
    </form>
</div>
@endsection
