@extends('layouts.app')

@section('title', 'Tableau de bord Administrateur')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-2">
            <!-- Sidebar -->
            <div class="card mb-4">
                <div class="card-header bg-dark text-white">
                    Administration
                </div>
                <div class="list-group list-group-flush">
                    <a href="#" class="list-group-item list-group-item-action active">Tableau de bord</a>
                    <a href="#" class="list-group-item list-group-item-action">Utilisateurs</a>
                    <a href="#" class="list-group-item list-group-item-action">Entreprises</a>
                    <a href="#" class="list-group-item list-group-item-action">Prestataires</a>
                    <a href="#" class="list-group-item list-group-item-action">Contrats</a>
                    <a href="#" class="list-group-item list-group-item-action">Événements</a>
                    <a href="#" class="list-group-item list-group-item-action">Paramètres</a>
                    <a href="#" class="list-group-item list-group-item-action">Logs système</a>
                </div>
            </div>
        </div>

        <div class="col-md-10">
            <!-- Main content -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h4 class="mb-0">Tableau de bord Administrateur</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-dark">
                        Bienvenue sur le tableau de bord d'administration. Vous avez tous les droits d'accès.
                    </div>

                    <!-- Statistics cards -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Utilisateurs</h5>
                                    <p class="card-text display-6">254</p>
                                </div>
                                <div class="card-footer bg-transparent border-top-0 text-center">
                                    <a href="#" class="btn btn-sm btn-light">Gérer</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Entreprises</h5>
                                    <p class="card-text display-6">42</p>
                                </div>
                                <div class="card-footer bg-transparent border-top-0 text-center">
                                    <a href="#" class="btn btn-sm btn-light">Gérer</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Prestataires</h5>
                                    <p class="card-text display-6">18</p>
                                </div>
                                <div class="card-footer bg-transparent border-top-0 text-center">
                                    <a href="#" class="btn btn-sm btn-light">Gérer</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Rapports</h5>
                                    <p class="card-text display-6">87</p>
                                </div>
                                <div class="card-footer bg-transparent border-top-0 text-center">
                                    <a href="#" class="btn btn-sm btn-light">Voir</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Latest users -->
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Derniers utilisateurs inscrits</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Nom</th>
                                                    <th>Type</th>
                                                    <th>Date</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>contact@techsolutions.fr</td>
                                                    <td><span class="badge bg-primary">Société</span></td>
                                                    <td>14/03/2025</td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-secondary">Voir</button>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>martin.durand@example.com</td>
                                                    <td><span class="badge bg-success">Employé</span></td>
                                                    <td>13/03/2025</td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-secondary">Voir</button>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>cabinet.medical@example.com</td>
                                                    <td><span class="badge bg-info">Prestataire</span></td>
                                                    <td>12/03/2025</td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-secondary">Voir</button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="card-footer bg-white text-end">
                                    <a href="#" class="btn btn-sm btn-primary">Voir tous les utilisateurs</a>
                                </div>
                            </div>
                        </div>

                        <!-- System activity -->
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Activité système</h5>
                                </div>
                                <div class="card-body">
                                    <div class="list-group">
                                        <div class="list-group-item">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1">Nouvelle société inscrite</h6>
                                                <small>14/03/2025 14:22</small>
                                            </div>
                                            <p class="mb-1">Tech Solutions a créé un compte entreprise.</p>
                                        </div>
                                        <div class="list-group-item">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1">Contrat créé</h6>
                                                <small>14/03/2025 10:15</small>
                                            </div>
                                            <p class="mb-1">Nouveau contrat entre Groupe Santé et Cabinet Médical.</p>
                                        </div>
                                        <div class="list-group-item">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1">Événement planifié</h6>
                                                <small>13/03/2025 16:45</small>
                                            </div>
                                            <p class="mb-1">Formation bien-être pour Digital Services le 30/03/2025.</p>
                                        </div>
                                        <div class="list-group-item">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1">Changement de statut prestataire</h6>
                                                <small>13/03/2025 09:30</small>
                                            </div>
                                            <p class="mb-1">Cabinet Bien-être validé comme prestataire officiel.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-white text-end">
                                    <a href="#" class="btn btn-sm btn-primary">Voir toutes les activités</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick actions -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Actions rapides</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <a href="#" class="btn btn-outline-primary w-100 py-3">
                                                <i class="bi bi-person-plus-fill mb-2 d-block fs-4"></i>
                                                Ajouter utilisateur
                                            </a>
                                        </div>
                                        <div class="col-md-3">
                                            <a href="#" class="btn btn-outline-success w-100 py-3">
                                                <i class="bi bi-building mb-2 d-block fs-4"></i>
                                                Nouvelle entreprise
                                            </a>
                                        </div>
                                        <div class="col-md-3">
                                            <a href="#" class="btn btn-outline-info w-100 py-3">
                                                <i class="bi bi-calendar-event mb-2 d-block fs-4"></i>
                                                Planifier événement
                                            </a>
                                        </div>
                                        <div class="col-md-3">
                                            <a href="#" class="btn btn-outline-secondary w-100 py-3">
                                                <i class="bi bi-gear-fill mb-2 d-block fs-4"></i>
                                                Configuration
                                            </a>
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

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
@endpush
