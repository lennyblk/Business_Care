@extends('layouts.app')

@section('title', 'Tableau de bord Client')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-md-3">
            <!-- Sidebar -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    Menu
                </div>
                <div class="list-group list-group-flush">
                    <a href="#" class="list-group-item list-group-item-action active">Tableau de bord</a>
                    <a href="#" class="list-group-item list-group-item-action">Profil</a>
                    <a href="{{ route('contracts') }}" class="list-group-item list-group-item-action">Contrats</a>
                    <a href="{{ route('events') }}" class="list-group-item list-group-item-action">Événements</a>
                    <a href="{{ route('medical') }}" class="list-group-item list-group-item-action">Rendez-vous médicaux</a>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <!-- Main content -->
            <div class="card">
                <div class="card-header bg-white">
                    <h4 class="mb-0">Tableau de bord Client</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        Bienvenue dans votre espace client.
                    </div>

                    <!-- Statistics cards -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Contrats</h5>
                                    <p class="card-text display-6">3</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Employés</h5>
                                    <p class="card-text display-6">12</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Événements</h5>
                                    <p class="card-text display-6">5</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent activity -->
                    <h5>Activité récente</h5>
                    <div class="list-group">
                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">Nouveau contrat ajouté</h6>
                                <small>Aujourd'hui</small>
                            </div>
                            <p class="mb-1">Contrat de prestation médicale signé.</p>
                        </div>
                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">Rendez-vous planifié</h6>
                                <small>Hier</small>
                            </div>
                            <p class="mb-1">Visite médicale pour 5 employés le 25/03/2025.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
