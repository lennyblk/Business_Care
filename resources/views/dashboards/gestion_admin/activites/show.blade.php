@extends('layouts.admin')

@section('content')
<div class="container">
    <h1>Détails de l'activité</h1>
    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4 font-weight-bold">Nom:</div>
                <div class="col-md-8">{{ $event->name }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4 font-weight-bold">Description:</div>
                <div class="col-md-8">{{ $event->description }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4 font-weight-bold">Date:</div>
                <div class="col-md-8">{{ $event->date }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4 font-weight-bold">Type d'événement:</div>
                <div class="col-md-8">{{ $event->event_type }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4 font-weight-bold">Capacité:</div>
                <div class="col-md-8">{{ $event->capacity }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4 font-weight-bold">Lieu:</div>
                <div class="col-md-8">{{ $event->location }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4 font-weight-bold">Inscriptions:</div>
                <div class="col-md-8">{{ $event->registrations }}</div>
            </div>
            <div class="text-right">
                <button onclick="window.history.back()" class="btn btn-secondary">Retour</button>
            </div>
        </div>
    </div>
</div>
@endsection
