@extends('layouts.app')

@section('title', 'Tableau de bord Prestataire')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-md-3">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    Menu
                </div>
                <div class="list-group list-group-flush">
                    <a href="#" class="list-group-item list-group-item-action active">Tableau de bord</a>
                    <a href="{{ route('profile.index') }}" class="list-group-item list-group-item-action">Profil prestataire</a>
                    <a href="{{ route('provider.evaluations.index') }}" class="list-group-item list-group-item-action">Suivi des évaluations</a>
                    <a href="#" class="list-group-item list-group-item-action">Clients</a>
                    <a href="#" class="list-group-item list-group-item-action">Calendrier</a>
                    <a href="#" class="list-group-item list-group-item-action">Facturation</a>
                    <a href="#" class="list-group-item list-group-item-action">Services</a>
                    <a href="{{ route('provider.assignments.index') }}" class="list-group-item list-group-item-action">Activités</a>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-white">
                    <h4 class="mb-0">Tableau de bord Prestataire</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-primary">
                        Bienvenue sur votre espace prestataire.
                    </div>

                    <!-- Statistics  -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Clients</h5>
                                    <p class="card-text display-6">8</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Services</h5>
                                    <p class="card-text display-6">12</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title">RDV prévus</h5>
                                    <p class="card-text display-6">15</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-dark">
                                <div class="card-body">
                                    <h5 class="card-title">En attente</h5>
                                    <p class="card-text display-6">3</p>
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
