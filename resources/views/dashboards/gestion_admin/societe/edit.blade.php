@extends('layouts.admin')

@section('content')
<div class="container">
    <h1>Modifier l'entreprise</h1>
    <form action="{{ route('admin.company.update', $company->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="name">Nom de l'entreprise</label>
            <input type="text" class="form-control" value="{{ $company->name }}" readonly>
            <small class="text-muted">Cette information ne peut être modifiée que par l'entreprise</small>
        </div>
        <div class="form-group">
            <label for="statut_compte">Statut du compte</label>
            <select name="statut_compte" id="statut_compte" class="form-control" required>
                <option value="Actif" {{ $company->statut_compte == 'Actif' ? 'selected' : '' }}>Actif</option>
                <option value="Inactif" {{ $company->statut_compte == 'Inactif' ? 'selected' : '' }}>Inactif</option>
            </select>
        </div>
        <div class="form-group">
            <label for="formule_abonnement">Formule d'abonnement</label>
            <select name="formule_abonnement" id="formule_abonnement" class="form-control" required>
                <option value="Starter" {{ $company->formule_abonnement == 'Starter' ? 'selected' : '' }}>Starter</option>
                <option value="Basic" {{ $company->formule_abonnement == 'Basic' ? 'selected' : '' }}>Basic</option>
                <option value="Premium" {{ $company->formule_abonnement == 'Premium' ? 'selected' : '' }}>Premium</option>
            </select>
        </div>
        <div class="form-group">
            <label for="date_debut_contrat">Date de début du contrat</label>
            <input type="date" name="date_debut_contrat" id="date_debut_contrat" class="form-control" value="{{ $company->date_debut_contrat }}">
        </div>
        <div class="form-group">
            <label for="date_fin_contrat">Date de fin du contrat</label>
            <input type="date" name="date_fin_contrat" id="date_fin_contrat" class="form-control" value="{{ $company->date_fin_contrat }}">
        </div>

        <!-- Informations en lecture seule -->
        <h3 class="mt-4">Informations de l'entreprise (lecture seule)</h3>
        <div class="form-group">
            <label>Adresse</label>
            <input type="text" class="form-control" value="{{ $company->address }}" readonly>
        </div>
        <div class="form-group">
            <label>Code Postal</label>
            <input type="text" class="form-control" value="{{ $company->code_postal }}" readonly>
        </div>
        <div class="form-group">
            <label>Ville</label>
            <input type="text" class="form-control" value="{{ $company->ville }}" readonly>
        </div>
        <div class="form-group">
            <label>Pays</label>
            <input type="text" class="form-control" value="{{ $company->pays }}" readonly>
        </div>
        <div class="form-group">
            <label>Téléphone</label>
            <input type="text" class="form-control" value="{{ $company->telephone }}" readonly>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" class="form-control" value="{{ $company->email }}" readonly>
        </div>
        <div class="form-group">
            <label>SIRET</label>
            <input type="text" class="form-control" value="{{ $company->siret }}" readonly>
        </div>
        <div class="form-group">
            <label>Date de Création</label>
            <input type="date" class="form-control" value="{{ $company->creation_date }}" readonly>
        </div>
        <div class="form-group">
            <label>Nombre d'employés</label>
            <input type="number" class="form-control" value="{{ $company->employee_count }}" readonly>
        </div>
        <div class="form-group">
            <label>Mode de paiement préféré</label>
            <input type="text" class="form-control" value="{{ $company->mode_paiement_prefere }}" readonly>
        </div>

        <div class="text-right">
            <button type="submit" class="btn btn-primary">Mettre à jour</button>
            <button onclick="window.history.back()" type="button" class="btn btn-secondary">Retour</button>
        </div>
    </form>
</div>
@endsection
