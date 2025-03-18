@extends('layouts.admin')

@section('title', 'Tableau de bord Administrateur')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-md-3">
            <!-- Sidebar -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    Menu Administrateur
                </div>
                <div class="list-group list-group-flush">
                    <a href="#" class="list-group-item list-group-item-action active">Tableau de bord</a>
                    <a href="#" class="list-group-item list-group-item-action">Gestion utilisateurs</a>
                    <a href="{{ route('admin.company') }}" class="list-group-item list-group-item-action">Entreprises</a>
                    <a href="{{ route('admin.salaries.index') }}" class="list-group-item list-group-item-action">Salariés</a>
                    <a href="{{ route('admin.prestataires.index') }}" class="list-group-item list-group-item-action">Prestataires</a>
                    <a href="{{ route('admin.activities.index') }}" class="list-group-item list-group-item-action">Activités</a>
                    <a href="#" class="list-group-item list-group-item-action">Configuration</a>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <!-- Main content -->
            <div class="card">
                <div class="card-header bg-white">
                    <h4 class="mb-0">Administration du système</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        Bienvenue dans l'interface d'administration.
                    </div>

                    <!-- Statistics cards -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Entreprises</h5>
                                    <p class="card-text display-6">{{ $companyCount }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Employés</h5>
                                    <p class="card-text display-6">{{ $employeeCount }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Prestataires</h5>
                                    <p class="card-text display-6">{{ $providerCount }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-dark">
                                <div class="card-body">
                                    <h5 class="card-title">Contrats</h5>
                                    <p class="card-text display-6">{{ $contractCount }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-secondary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Activités</h5>
                                    <p class="card-text display-6">{{ $activityCount }}</p>
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

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
@endpush
