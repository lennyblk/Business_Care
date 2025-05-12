@extends('layouts.admin')

@section('title', 'Mon profil employé')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-md-3">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    Menu
                </div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('dashboard.employee') }}" class="list-group-item list-group-item-action">Tableau de bord</a>
                    <a href="{{ route('profile.index') }}" class="list-group-item list-group-item-action active">Profil</a>
                    <a href="{{ route('employee.events.index') }}" class="list-group-item list-group-item-action">Événements</a>
                    <!-- Autres liens spécifiques aux employés -->
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Mon profil employé</h4>
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

                    <!-- Informations personnelles -->
                    <div class="card mb-4">
                        <div class="card-header">
                            Informations personnelles
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Prénom:</strong> {{ $profile['first_name'] }}</p>
                                    <p><strong>Nom:</strong> {{ $profile['last_name'] }}</p>
                                    <p><strong>Email:</strong> {{ $profile['email'] }}</p>
                                    <p><strong>Téléphone:</strong> {{ $profile['telephone'] ?? 'Non renseigné' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Fonction:</strong> {{ $profile['function'] ?? 'Non renseignée' }}</p>
                                    <p><strong>Département:</strong> {{ $profile['department'] ?? 'Non renseigné' }}</p>
                                    <p><strong>Date d'inscription:</strong> {{ isset($profile['created_at']) ? \Carbon\Carbon::parse($profile['created_at'])->format('d/m/Y') : 'Non disponible' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Entreprise -->
                    <div class="card mb-4">
                        <div class="card-header">
                            Entreprise
                        </div>
                        <div class="card-body">
                            <p><strong>Entreprise:</strong> {{ $profile['company']['name'] ?? 'Non disponible' }}</p>
                            @if(isset($profile['company']))
                                <p><strong>Adresse:</strong> {{ $profile['company']['address'] ?? 'Non disponible' }}</p>
                                <p><strong>Ville:</strong> {{ $profile['company']['ville'] ?? 'Non disponible' }}</p>
                                <p><strong>Pays:</strong> {{ $profile['company']['pays'] ?? 'Non disponible' }}</p>
                            @endif
                        </div>
                    </div>

                    <!-- Statistiques d'activité -->
                    <div class="card mb-4">
                        <div class="card-header">
                            Statistiques d'activité
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 text-center mb-3">
                                    <div class="card bg-primary text-white h-100">
                                        <div class="card-body">
                                            <h5 class="card-title">Événements auxquels vous êtes inscrit</h5>
                                            <p class="display-4">{{ $profile['events_count'] ?? 0 }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 text-center mb-3">
                                    <div class="card bg-success text-white h-100">
                                        <div class="card-body">
                                            <h5 class="card-title">Consultations médicales</h5>
                                            <p class="display-4">{{ $profile['medical_appointments_count'] ?? 0 }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 text-center mb-3">
                                    <div class="card bg-info text-white h-100">
                                        <div class="card-body">
                                            <h5 class="card-title">Communautés</h5>
                                            <p class="display-4">{{ $profile['communities_count'] ?? 0 }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
    /* Badges et styles pour les cartes */
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

    .bg-primary {
        background-color: #0d6efd !important;
    }

    .bg-info {
        background-color: #0dcaf0 !important;
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

    .display-4 {
        font-size: 3.5rem;
        font-weight: 300;
        line-height: 1.2;
    }
</style>
@endsection
