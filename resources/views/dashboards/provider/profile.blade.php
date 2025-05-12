@extends('layouts.admin')

@section('title', 'Mon profil prestataire')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-md-3">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    Menu
                </div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('dashboard.provider') }}" class="list-group-item list-group-item-action">Tableau de bord</a>
                    <a href="{{ route('profile.index') }}" class="list-group-item list-group-item-action active">Profil</a>
                    <a href="{{ route('provider.assignments.index') }}" class="list-group-item list-group-item-action">Missions</a>
                    <a href="#" class="list-group-item list-group-item-action">Disponibilités</a>
                    <a href="#" class="list-group-item list-group-item-action">Facturation</a>
                    <!-- Autres liens spécifiques aux prestataires -->
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Mon profil prestataire</h4>
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
                                    <p><strong>Nom/Raison sociale:</strong> {{ $profile['name'] }}</p>
                                    <p><strong>Email:</strong> {{ $profile['email'] }}</p>
                                    <p><strong>Téléphone:</strong> {{ $profile['telephone'] }}</p>
                                    <p><strong>Spécialité:</strong> {{ $profile['speciality'] }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Adresse:</strong> {{ $profile['address'] ?? 'Non renseignée' }}</p>
                                    <p><strong>Site web:</strong> {{ $profile['website'] ?? 'Non renseigné' }}</p>
                                    <p><strong>Date d'inscription:</strong> {{ isset($profile['created_at']) ? \Carbon\Carbon::parse($profile['created_at'])->format('d/m/Y') : 'Non disponible' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    @if(isset($profile['description']) && !empty($profile['description']))
                    <div class="card mb-4">
                        <div class="card-header">
                            Description
                        </div>
                        <div class="card-body">
                            <p>{{ $profile['description'] }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Statistiques -->
                    <div class="card mb-4">
                        <div class="card-header">
                            Statistiques
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 text-center mb-3">
                                    <div class="card bg-primary text-white h-100">
                                        <div class="card-body">
                                            <h5 class="card-title">Missions réalisées</h5>
                                            <p class="display-4">{{ $profile['completed_assignments_count'] ?? 0 }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 text-center mb-3">
                                    <div class="card bg-info text-white h-100">
                                        <div class="card-body">
                                            <h5 class="card-title">Missions en cours</h5>
                                            <p class="display-4">{{ $profile['ongoing_assignments_count'] ?? 0 }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 text-center mb-3">
                                    <div class="card bg-success text-white h-100">
                                        <div class="card-body">
                                            <h5 class="card-title">Note moyenne</h5>
                                            <p class="display-4">{{ number_format($profile['average_rating'] ?? 0, 1) }}/5</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dernières évaluations -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span>Dernières évaluations</span>
                            <a href="#" class="btn btn-sm btn-outline-primary">Voir toutes</a>
                        </div>
                        <div class="card-body">
                            @if(isset($profile['recent_evaluations']) && count($profile['recent_evaluations']) > 0)
                                @foreach($profile['recent_evaluations'] as $evaluation)
                                    <div class="mb-3 p-3 border rounded">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <p class="mb-0 fw-bold">{{ $evaluation['title'] }}</p>
                                                <p class="text-muted small">{{ \Carbon\Carbon::parse($evaluation['date'])->format('d/m/Y') }}</p>
                                            </div>
                                            <div>
                                                @for($i = 1; $i <= 5; $i++)
                                                    @if($i <= $evaluation['rating'])
                                                        <i class="fas fa-star text-warning"></i>
                                                    @else
                                                        <i class="far fa-star text-warning"></i>
                                                    @endif
                                                @endfor
                                            </div>
                                        </div>
                                        <p class="mt-2">{{ $evaluation['content'] }}</p>
                                    </div>
                                @endforeach
                            @else
                                <p class="text-muted">Aucune évaluation pour le moment.</p>
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

    .fw-bold {
        font-weight: 700 !important;
    }

    .text-warning {
        color: #ffc107 !important;
    }

    .text-muted {
        color: #6c757d !important;
    }
</style>
@endsection
