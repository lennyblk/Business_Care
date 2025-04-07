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
                    <a href="{{ route('dashboard.client') }}" class="list-group-item list-group-item-action active">Tableau de bord</a>
                    <a href="#" class="list-group-item list-group-item-action">Profil</a>
                    <a href="{{ route('contracts.index') }}" class="list-group-item list-group-item-action">Contrats</a>
                    <a href="{{ route('quotes.index') }}" class="list-group-item list-group-item-action">Devis</a>
                    <a href="{{ route('employees.index') }}" class="list-group-item list-group-item-action">Collaborateurs</a>
                    <a href="{{ route('payments.index') }}" class="list-group-item list-group-item-action">Paiements</a>
                    <a href="{{ route('invoices.index') }}" class="list-group-item list-group-item-action">Facturation</a>
                </div>
            </div>
            
            <!-- Informations de la société -->
            @if(auth()->user()->company)
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    Votre société
                </div>
                <div class="card-body">
                    <h5 class="card-title">{{ auth()->user()->company->name }}</h5>
                    <p class="card-text">
                        <small>{{ auth()->user()->company->address }}<br>
                        {{ auth()->user()->company->postal_code }} {{ auth()->user()->company->city }}<br>
                        {{ auth()->user()->company->country }}</small>
                    </p>
                </div>
            </div>
            @endif
        </div>

        <div class="col-md-9">
            <!-- Main content -->
            <div class="card">
                <div class="card-header bg-white">
                    <h4 class="mb-0">Tableau de bord Client</h4>
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
                    
                    @if(!auth()->user()->company)
                    <div class="alert alert-warning">
                        Vous n'êtes pas encore associé à une société. Veuillez contacter l'administration pour configurer votre compte.
                    </div>
                    @else
                    <div class="alert alert-info">
                        Bienvenue dans votre espace client, {{ auth()->user()->name }}.
                    </div>

                    <!-- Quick Actions -->
                    <div class="mb-4">
                        <h5>Actions rapides</h5>
                        <div class="btn-group">
                            <a href="{{ route('quotes.create') }}" class="btn btn-primary">Nouveau devis</a>
                            <a href="{{ route('contracts.create') }}" class="btn btn-success">Nouveau contrat</a>
                            <a href="{{ route('employees.create') }}" class="btn btn-info">Ajouter un collaborateur</a>
                        </div>
                    </div>

                    <!-- Statistics cards -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Contrats actifs</h5>
                                    <p class="card-text display-6">{{ $activeContracts }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Collaborateurs</h5>
                                    <p class="card-text display-6">{{ $employeesCount }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Devis en cours</h5>
                                    <p class="card-text display-6">{{ $pendingQuotes }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Factures à payer</h5>
                                    <p class="card-text display-6">{{ $unpaidInvoices }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent activity -->
                    <h5>Activités récentes</h5>
                    <div class="list-group">
                        @forelse($recentActivities as $activity)
                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">{{ $activity->title }}</h6>
                                <small>{{ $activity->created_at->diffForHumans() }}</small>
                            </div>
                            <p class="mb-1">{{ $activity->description }}</p>
                        </div>
                        @empty
                        <div class="list-group-item">
                            <p class="mb-1">Aucune activité récente</p>
                        </div>
                        @endforelse
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection