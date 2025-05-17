@extends('layouts.admin')

@section('content')
<div class="container">
    <h1>Créer une nouvelle activité</h1>
    <form action="{{ route('admin.activities.store') }}" method="POST">
        @csrf
        <div class="form-group mb-3">
            <label for="activity_type" class="form-label">Type d'activité *</label>
            <select name="activity_type" id="activity_type" class="form-select" required>
                <option value="">-- Sélectionnez un type d'activité --</option>
                @foreach($activityTypes as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group mb-3">
            <label for="company_id">Entreprise *</label>
            <select name="company_id" id="company_id" class="form-select" required>
                <option value="">Sélectionner une entreprise</option>
                @foreach($companies as $company)
                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group mb-3">
            <label for="proposed_date" class="form-label">Date souhaitée *</label>
            <input type="date" name="proposed_date" id="proposed_date" class="form-control"
                   value="{{ old('proposed_date') }}" min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
        </div>

        <div class="form-group mb-3">
            <label for="location_id" class="form-label">Lieu de l'activité *</label>
            <select name="location_id" id="location_id" class="form-select" required>
                <option value="">-- Sélectionnez un lieu --</option>
                @foreach($locations as $location)
                    <option value="{{ $location->id }}">{{ $location->name }} ({{ $location->city }})</option>
                @endforeach
            </select>
        </div>

        <div class="form-group mb-3">
            <label for="duration" class="form-label">Durée de l'activité (en minutes) *</label>
            <input type="number" name="duration" id="duration" class="form-control"
                value="{{ old('duration', 60) }}" min="30" max="480" step="30" required>
            <small class="text-muted">Minimum: 30 minutes, Maximum: 8 heures</small>
        </div>

        <div class="form-group mb-3">
            <label for="notes">Notes ou précisions</label>
            <textarea name="notes" id="notes" class="form-control" rows="4"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Créer</button>
        <a href="{{ route('admin.activities.index') }}" class="btn btn-secondary">Retour</a>
    </form>
</div>
@endsection
