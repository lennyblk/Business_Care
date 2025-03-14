@extends('layouts.app')

@section('title', 'Tableau de bord Prestataire')

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
                    <a href="#" class="list-group-item list-group-item-action">Profil prestataire</a>
                    <a href="#" class="list-group-item list-group-item-action">Clients</a>
                    <a href="#" class="list-group-item list-group-item-action">Calendrier</a>
                    <a href="#" class="list-group-item list-group-item-action">Facturation</a>
                    <a href="#" class="list-group-item list-group-item-action">Services</a>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <!-- Main content -->
            <div class="card">
                <div class="card-header bg-white">
                    <h4 class="mb-0">Tableau de bord Prestataire</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-primary">
                        Bienvenue sur votre espace prestataire.
                    </div>

                    <!-- Statistics cards -->
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

                    <!-- Calendrier des rendez-vous -->
                    <h5 class="mb-3">Planning des rendez-vous</h5>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Client</th>
                                    <th>Date</th>
                                    <th>Heure</th>
                                    <th>Service</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Entreprise XYZ</td>
                                    <td>20/03/2025</td>
                                    <td>09:00 - 12:00</td>
                                    <td>Évaluation ergonomique</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary">Modifier</button>
                                        <button class="btn btn-sm btn-outline-danger">Annuler</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Tech Solutions</td>
                                    <td>22/03/2025</td>
                                    <td>14:00 - 16:00</td>
                                    <td>Consultation bien-être</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary">Modifier</button>
                                        <button class="btn btn-sm btn-outline-danger">Annuler</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Groupe Santé</td>
                                    <td>25/03/2025</td>
                                    <td>10:00 - 15:00</td>
                                    <td>Atelier prévention</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary">Modifier</button>
                                        <button class="btn btn-sm btn-outline-danger">Annuler</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Nouvelles demandes -->
                    <h5 class="mb-3">Nouvelles demandes</h5>
                    <div class="list-group">
                        <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Digital Services</strong> - Consultation de santé au travail
                                <div class="text-muted small">Demande reçue le 18/03/2025</div>
                            </div>
                            <div>
                                <button class="btn btn-sm btn-success me-1">Accepter</button>
                                <button class="btn btn-sm btn-danger">Refuser</button>
                            </div>
                        </div>
                        <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Groupe Finance</strong> - Évaluation du stress
                                <div class="text-muted small">Demande reçue le 17/03/2025</div>
                            </div>
                            <div>
                                <button class="btn btn-sm btn-success me-1">Accepter</button>
