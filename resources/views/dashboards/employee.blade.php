@extends('layouts.app')

@section('title', 'Tableau de bord Employé')

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
                    <a href="#" class="list-group-item list-group-item-action">Mon profil</a>
                    <a href="#" class="list-group-item list-group-item-action">Planning médical</a>
                    <a href="{{ route('employee.events.index') }}" class="list-group-item list-group-item-action">Mes événements</a>
                    <a href="#" class="list-group-item list-group-item-action">Assistance</a>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-white">
                    <h4 class="mb-0">Tableau de bord Employé</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-success">
                        @if(isset($employee))
                            Bienvenue sur votre espace personnel, {{ $employee->first_name }} {{ $employee->last_name }}.
                        @else
                            Bienvenue sur votre espace personnel.
                        @endif
                    </div>

                    <!-- Statistics  -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">RDV Médicaux</h5>
                                    <p class="card-text display-6">0</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Événements</h5>
                                    <p class="card-text display-6">{{ $eventsCount ?? 0 }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Upcoming events -->
                    <h5 class="mb-3">Événements à venir</h5>
                    @if(isset($upcomingEvents) && $upcomingEvents->count() > 0)
                        <div class="list-group">
                            @foreach($upcomingEvents as $event)
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">{{ $event->name }}</h6>
                                    <small>{{ date('d/m/Y', strtotime($event->date)) }}</small>
                                </div>
                                <p class="mb-1">{{ \Illuminate\Support\Str::limit($event->description, 100) }}</p>
                                <small class="text-muted">Type: {{ $event->event_type }} | Lieu: {{ $event->location }}</small>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-info">
                            Vous n'avez pas d'événements à venir. <a href="{{ route('employee.events.index') }}">Parcourir les événements disponibles</a>.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
