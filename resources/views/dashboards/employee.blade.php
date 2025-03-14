@extends('layouts.app')

@section('title', 'Tableau de bord Employé')

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
                    <a href="#" class="list-group-item list-group-item-action">Mon profil</a>
                    <a href="#" class="list-group-item list-group-item-action">Planning médical</a>
                    <a href="#" class="list-group-item list-group-item-action">Mes événements</a>
                    <a href="#" class="list-group-item list-group-item-action">Assistance</a>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <!-- Main content -->
            <div class="card">
                <div class="card-header bg-white">
                    <h4 class="mb-0">Tableau de bord Employé</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-success">
                        Bienvenue sur votre espace personnel.
                    </div>

                    <!-- Statistics cards -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">RDV Médicaux</h5>
                                    <p class="card-text display-6">2</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Événements</h5>
                                    <p class="card-text display-6">4</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-warning text-dark">
                                <div class="card-body">
                                    <h5 class="card-title">Notifications</h5>
                                    <p class="card-text display-6">3</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Upcoming appointments -->
                    <h5 class="mb-3">Prochains rendez-vous</h5>
                    <div class="table-responsive mb-4">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Lieu</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>24/03/2025</td>
                                    <td>Visite annuelle</td>
                                    <td>Centre médical</td>
                                    <td><span class="badge bg-primary">Confirmé</span></td>
                                </tr>
                                <tr>
                                    <td>15/04/2025</td>
                                    <td>Ergonomie</td>
                                    <td>Bureau</td>
                                    <td><span class="badge bg-warning text-dark">En attente</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Upcoming events -->
                    <h5 class="mb-3">Événements à venir</h5>
                    <div class="list-group">
                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">Formation bien-être</h6>
                                <small>30/03/2025</small>
                            </div>
                            <p class="mb-1">Atelier de gestion du stress et exercices de relaxation.</p>
                        </div>
                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">Team building</h6>
                                <small>12/04/2025</small>
                            </div>
                            <p class="mb-1">Journée d'activités en plein air avec l'équipe.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
