@extends('layouts.admin')

@section('content')
<div class="container">
    <h1>Modifier l'activité</h1>
    <form action="{{ route('admin.activities.update', $event->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="name">Nom</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ $event->name }}" required>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" id="description" class="form-control">{{ $event->description }}</textarea>
        </div>
        <div class="form-group">
            <label for="date">Date</label>
            <input type="datetime-local" name="date" id="date" class="form-control" value="{{ $event->date }}" required>
        </div>
        <div class="form-group">
            <label for="event_type">Type d'événement</label>
            <select name="event_type" id="event_type" class="form-control" required>
                <option value="Webinar" {{ $event->event_type == 'Webinar' ? 'selected' : '' }}>Webinar</option>
                <option value="Conference" {{ $event->event_type == 'Conference' ? 'selected' : '' }}>Conference</option>
                <option value="Sport Event" {{ $event->event_type == 'Sport Event' ? 'selected' : '' }}>Sport Event</option>
                <option value="Workshop" {{ $event->event_type == 'Workshop' ? 'selected' : '' }}>Workshop</option>
            </select>
        </div>
        <div class="form-group">
            <label for="capacity">Capacité</label>
            <input type="number" name="capacity" id="capacity" class="form-control" value="{{ $event->capacity }}" required>
        </div>
        <div class="form-group">
            <label for="location">Lieu</label>
            <input type="text" name="location" id="location" class="form-control" value="{{ $event->location }}">
        </div>
        <div class="form-group">
            <label for="company_id">Entreprise</label>
            <select name="company_id" id="company_id" class="form-control" required>
                <option value="">Sélectionner une entreprise</option>
                @foreach($companies as $company)
                    <option value="{{ $company->id }}" {{ $event->company_id == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Mettre à jour</button>
        <button onclick="window.history.back()" type="button" class="btn btn-secondary">Retour</button>
    </form>
</div>
@endsection
