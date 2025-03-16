@extends('layouts.admin')

@section('content')
<div class="container">
    <h1>Modifier l'entreprise</h1>
    <form action="{{ route('admin.company.update', $company->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="name">Nom de l'entreprise</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ $company->name }}" required>
        </div>
        <div class="form-group">
            <label for="address">Adresse</label>
            <input type="text" name="address" id="address" class="form-control" value="{{ $company->address }}" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" class="form-control" value="{{ $company->email }}" required>
        </div>
        <div class="form-group">
            <label for="formule_abonnement">Formule d'abonnement</label>
            <input type="text" name="formule_abonnement" id="formule_abonnement" class="form-control" value="{{ $company->formule_abonnement }}" required>
        </div>
        <div class="form-group">
            <label for="statut_compte">Statut du compte</label>
            <input type="text" name="statut_compte" id="statut_compte" class="form-control" value="{{ $company->statut_compte }}" required>
        </div>
        <button type="submit" class="btn btn-primary">Mettre à jour</button>
    </form>
</div>
@endsection
