@extends('layouts.admin')

@section('title', 'Tableau de bord Administrateur')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-md-3">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    Menu Administrateur
                </div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('admin.company') }}" class="list-group-item list-group-item-action">Entreprises</a>
                    <a href="{{ route('admin.salaries.index') }}" class="list-group-item list-group-item-action">Salariés</a>
                    <a href="{{ route('admin.prestataires.index') }}" class="list-group-item list-group-item-action" target="_self">Prestataires</a>
                    <a href="{{ route('admin.activities.index') }}" class="list-group-item list-group-item-action">Activités</a>
                    <a href="{{ route('admin.inscriptions.index') }}" class="list-group-item list-group-item-action">Inscriptions en attente</a>
                    <a href="{{ route('admin.contracts.index') }}" class="list-group-item list-group-item-action">Contrats en attente</a>
                    <a href="{{ route('admin.event_proposals.index') }}" class="list-group-item list-group-item-action">Demandes d'activités</a>
                    <a href="{{ route('admin.advice.index') }}" class="list-group-item list-group-item-action">Conseils</a>
                    <a href="{{ route('admin.invoices.index') }}" class="list-group-item list-group-item-action">Facturation</a>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-white">
                    <h4 class="mb-0">Administration du système</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        Bienvenue dans l'interface d'administration.
                    </div>

                    <!-- Statistics -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <div class="card text-white">
                                <div class="card-body">
                                    <h5 class="card-title text-black">Entreprises</h5>
                                    <p class="card-text display-6 text-black">{{ $companyCount }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-white">
                                <div class="card-body">
                                    <h5 class="card-title text-black">Employés</h5>
                                    <p class="card-text display-6 text-black">{{ $employeeCount }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-white">
                                <div class="card-body">
                                    <h5 class="card-title text-black">Prestataires</h5>
                                    <p class="card-text display-6 text-black">{{ $providerCount }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-dark">
                                <div class="card-body">
                                    <h5 class="card-title text-black">Contrats</h5>
                                    <p class="card-text display-6 text-black">{{ $contractCount }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-white">
                                <div class="card-body">
                                    <h5 class="card-title text-black">Activités</h5>
                                    <p class="card-text display-6 text-black">{{ $activityCount }}</p>
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
