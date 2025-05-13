@extends('layouts.app')

@section('content')
<div class="container">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
        </div>
    @endif

    @if (session('warning'))
        <div class="alert alert-warning alert-dismissible fade show mt-3" role="alert">
            {{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Événements de mon entreprise</h1>
        <a href="{{ route('employee.events.history') }}" class="btn btn-info">
            Voir l'historique de mes événements
        </a>
    </div>

    <div class="row">
        @foreach ($allEvents->where('company_id', $employee->company_id) as $event)
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title">{{ $event->name }}</h5>
                    <p class="card-text">{{ Str::limit($event->description, 100) }}</p>
                    <p><strong>Date :</strong> {{ $event->date }}</p>
                    <p><strong>Type :</strong> {{ $event->event_type }}</p>
                    <p><strong>Lieu :</strong> {{ $event->location }}</p>
                    <p>
                        <strong>Places disponibles :</strong>
                        {{ $event->capacity - $event->registrations }} / {{ $event->capacity }}
                    </p>

                    @php
                        $isRegistered = \App\Models\EventRegistration::where('event_id', $event->id)
                                        ->where('employee_id', $employee->id)
                                        ->exists();
                        $isFull = $event->registrations >= $event->capacity;
                    @endphp

                    <div class="mt-auto">
                        @if ($isRegistered)
                            <form action="{{ route('employee.events.cancel', $event->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-danger">Se désinscrire</button>
                            </form>
                        @elseif ($isFull)
                            <button class="btn btn-secondary" disabled>Complet</button>
                        @else
                            <form action="{{ route('employee.events.register', $event->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success">S'inscrire</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <h1 class="my-4">Mes Événements</h1>

    @if ($myEvents->count() > 0)
        <div class="row">
            @foreach ($myEvents as $event)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 border-success">
                    <div class="card-body d-flex flex-column">
                        <span class="badge bg-success float-end">Inscrit</span>
                        <h5 class="card-title">{{ $event->name }}</h5>
                        <p class="card-text">{{ Str::limit($event->description, 100) }}</p>
                        <p><strong>Date :</strong> {{ $event->date }}</p>
                        <p><strong>Type :</strong> {{ $event->event_type }}</p>
                        <p><strong>Lieu :</strong> {{ $event->location }}</p>

                        <div class="mt-auto">
                            <form action="{{ route('employee.events.cancel', $event->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-danger">Se désinscrire</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @else
        <div class="alert alert-info">
            Vous n'êtes inscrit à aucun événement pour le moment.
        </div>
    @endif
</div>
@endsection
