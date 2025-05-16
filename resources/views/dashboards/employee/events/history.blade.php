@extends('layouts.app')

@section('content')
<div class="container py-4">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 mb-0">Historique des événements</h1>
        <a href="{{ route('employee.events.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-2"></i>Retour aux événements
        </a>
    </div>

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        @forelse($events as $event)
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title text-primary mb-3">{{ $event->name }}</h5>
                        <p class="card-text text-muted mb-2">{{ $event->description }}</p>
                        
                        <div class="d-flex align-items-center mb-2">
                            <i class="far fa-calendar-alt text-secondary me-2"></i>
                            <span>{{ \Carbon\Carbon::parse($event->date)->format('d/m/Y H:i') }}</span>
                        </div>
                        
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-map-marker-alt text-secondary me-2"></i>
                            <span>{{ $event->location }}</span>
                        </div>

                        <div class="card-footer bg-transparent border-0 p-0">
                            @if(!$event->hasEvaluation)
                                <a href="{{ route('employee.events.evaluate', $event->id) }}" 
                                   class="btn btn-primary w-100">
                                    <i class="fas fa-star me-2"></i>Évaluer
                                </a>
                            @else
                                <div class="alert alert-success mb-0 text-center py-2">
                                    <i class="fas fa-check-circle me-2"></i>Déjà évalué
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle me-2"></i>
                    Aucun événement dans l'historique
                </div>
            </div>
        @endforelse
    </div>
</div>

@push('styles')
<style>
    .card {
        transition: transform 0.2s ease-in-out;
        border: none;
        border-radius: 10px;
    }
    .card:hover {
        transform: translateY(-5px);
    }
    .btn-outline-primary {
        border-radius: 20px;
    }
    .alert {
        border-radius: 10px;
    }
    .card-title {
        font-weight: 600;
    }
</style>
@endpush
@endsection
