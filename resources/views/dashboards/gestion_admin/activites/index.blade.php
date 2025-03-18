@extends('layouts.admin')

@section('content')
<div class="container">
    <h1 class="mb-5">Gestion des activités</h1>
    <button onclick="window.location='{{ route('admin.activities.create') }}'" class="btn btn-primary mb-3">Créer une activité</button>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Description</th>
                <th>Date</th>
                <th>Type d'événement</th>
                <th>Capacité</th>
                <th>Lieu</th>
                <th>Inscriptions</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($events as $event)
            <tr>
                <td>{{ $event->name }}</td>
                <td>{{ $event->description }}</td>
                <td>{{ $event->date }}</td>
                <td>{{ $event->event_type }}</td>
                <td>{{ $event->capacity }}</td>
                <td>{{ $event->location }}</td>
                <td>{{ $event->registrations }}</td>
                <td>
                    <button onclick="window.location='{{ route('admin.activities.show', $event->id) }}'" class="btn btn-info">Détails</button>
                    <button onclick="window.location='{{ route('admin.activities.edit', $event->id) }}'" class="btn btn-warning">Modifier</button>
                    <form action="{{ route('admin.activities.destroy', $event->id) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Supprimer</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="text-right">
        <button onclick="window.history.back()" class="btn btn-secondary">Retour</button>
    </div>
</div>
@endsection
