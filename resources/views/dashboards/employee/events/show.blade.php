@extends('layouts.app')

@section('content')
<div class="container">
    <div class="my-4">
        <a href="{{ route('employee.events.index') }}" class="btn btn-secondary mb-3">
            <i class="fas fa-arrow-left"></i> Retour aux événements
        </a>

        <div class="card">
            <div class="card-body">
                <h2 class="card-title">{{ $event->name }}</h2>
                <div class="card-text mb-4">
                    <p>{{ $event->description }}</p>
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <p><strong>Date :</strong> {{ $event->date }}</p>
                            <p><strong>Type :</strong> {{ $event->event_type }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Lieu :</strong> {{ $event->location }}</p>
                            <p><strong>Places disponibles :</strong> {{ $event->capacity - $event->registrations }}</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
