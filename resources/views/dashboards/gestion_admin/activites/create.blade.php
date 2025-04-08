@extends('layouts.admin')

@section('content')
<div class="container">
    <h1>Créer une nouvelle activité</h1>
    <form action="{{ route('admin.activities.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="name">Nom</label>
            <input type="text" name="name" id="name" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" id="description" class="form-control"></textarea>
        </div>
        <div class="form-group">
            <label for="date">Date</label>
            <input type="datetime-local" name="date" id="date" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="event_type">Type d'événement</label>
            <select name="event_type" id="event_type" class="form-control" required>
                <option value="Webinar">Webinar</option>
                <option value="Conference">Conference</option>
                <option value="Sport Event">Sport Event</option>
                <option value="Workshop">Workshop</option>
            </select>
        </div>
        <div class="form-group">
            <label for="capacity">Capacité</label>
            <input type="number" name="capacity" id="capacity" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="location">Lieu</label>
            <input type="text" name="location" id="location" class="form-control">
        </div>
        <div class="form-group">
            <label for="company_id">Entreprise</label>
            <select name="company_id" id="company_id" class="form-control mb-4" required>
                <option value="">Sélectionner une entreprise</option>
                @foreach($companies as $company)
                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Créer</button>
        <button onclick="window.history.back()" type="button" class="btn btn-secondary">Retour</button>
    </form>
</div>
@endsection
