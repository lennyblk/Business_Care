@extends('layouts.admin')

@section('title', 'Mon profil entreprise')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-md-3">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    Menu
                </div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('dashboard.client') }}" class="list-group-item list-group-item-action">Tableau de bord</a>
                    <a href="{{ route('profile.index') }}" class="list-group-item list-group-item-action active">Profil</a>
                    <a href="{{ route('contracts.index') }}" class="list-group-item list-group-item-action">Contrats</a>
                    <a href="{{ route('quotes.index') }}" class="list-group-item list-group-item-action">Devis</a>
                    <a href="{{ route('employees.index') }}" class="list-group-item list-group-item-action">Collaborateurs</a>
                    <a href="{{ route('invoices.index') }}" class="list-group-item list-group-item-action">Facturation</a>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Mon profil entreprise</h4>
                    <div>
                        <a href="{{ route('profile.edit') }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Modifier mes informations
                        </a>
                        <a href="{{ route('profile.password') }}" class="btn btn-secondary">
                            <i class="fas fa-key"></i> Changer de mot de passe
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Alertes et notifications -->
                    @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                    @endif

                    @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                    @endif

                    <!-- Informations générales -->
                    <div class="card mb-4">
                        <div class="card-header">
                            Informations générales
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Nom de l'entreprise:</strong> {{ $profile['name'] }}</p>
                                    <p><strong>Email:</strong> {{ $profile['email'] }}</p>
                                    <p><strong>Téléphone:</strong> {{ $profile['telephone'] }}</p>
                                    <p><strong>SIRET:</strong> {{ $profile['siret'] ?? 'Non renseigné' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Effectif:</strong> {{ $profile['effectif'] ?? 'Non renseigné' }}</p>
                                    <p><strong>Secteur d'activité:</strong> {{ $profile['secteur_activite'] ?? 'Non renseigné' }}</p>
                                    <p><strong>Date d'inscription:</strong> {{ isset($profile['created_at']) ? \Carbon\Carbon::parse($profile['created_at'])->format('d/m/Y') : 'Non disponible' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Adresse -->
                    <div class="card mb-4">
                        <div class="card-header">
                            Adresse
                        </div>
                        <div class="card-body">
                            <p><strong>Adresse:</strong> {{ $profile['address'] }}</p>
                            <p><strong>Code postal:</strong> {{ $profile['code_postal'] }}</p>
                            <p><strong>Ville:</strong> {{ $profile['ville'] }}</p>
                            <p><strong>Pays:</strong> {{ $profile['pays'] }}</p>
                        </div>
                    </div>

                    <!-- Informations d'abonnement -->
                    <div class="card mb-4">
                        <div class="card-header">
                            Informations d'abonnement
                        </div>
                        <div class="card-body">
                            <p><strong>Formule:</strong> {{ $profile['formule_abonnement'] ?? 'Aucun abonnement actif' }}</p>
                            @if(isset($profile['date_fin_contrat']))
                                <p><strong>Date de fin:</strong> {{ \Carbon\Carbon::parse($profile['date_fin_contrat'])->format('d/m/Y') }}</p>
                                <p><strong>Statut:</strong>
                                    @if($profile['statut_compte'] === 'Actif')
                                        <span class="badge bg-success">Actif</span>
                                    @elseif($profile['statut_compte'] === 'En attente')
                                        <span class="badge bg-warning">En attente</span>
                                    @elseif($profile['statut_compte'] === 'Inactif')
                                        <span class="badge bg-danger">Inactif</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $profile['statut_compte'] }}</span>
                                    @endif
                                </p>
                            @else
                                <p>Aucun abonnement actif. <a href="{{ route('quotes.create') }}">Créer un devis</a> pour commencer.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    /* Badges */
    .badge {
        display: inline-block;
        padding: 0.35em 0.65em;
        font-size: 0.75em;
        font-weight: 700;
        line-height: 1;
        color: #fff;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border-radius: 0.25rem;
    }

    .bg-success {
        background-color: #198754 !important;
    }

    .bg-warning {
        background-color: #ffc107 !important;
        color: #000;
    }

    .bg-danger {
        background-color: #dc3545 !important;
    }

    .bg-secondary {
        background-color: #6c757d !important;
    }
</style>
@endsection
