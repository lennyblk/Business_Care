@extends('layouts.admin')

@section('title', 'Évaluations de mes prestations')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h2">Évaluations de mes prestations</h1>
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <h5 class="card-title">Note moyenne</h5>
                        <p class="card-text display-4">{{ number_format($averageRating, 1) }}/5</p>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6>Total des évaluations</h6>
                            <p class="h3">{{ $totalEvaluations }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6>Évènements évalués</h6>
                            <p class="h3">{{ $evaluatedEvents }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="h4 mb-4">Dernières évaluations</h2>
                    
                    @if($evaluations->count() > 0)
                        @foreach($evaluations as $evaluation)
                        <div class="border-bottom mb-3 pb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h3 class="h5 mb-0">{{ $evaluation->event->name }}</h3>
                                <div class="d-flex align-items-center">
                                    <div class="me-2">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= $evaluation->rating)
                                                <i class="fas fa-star text-warning"></i>
                                            @else
                                                <i class="far fa-star text-warning"></i>
                                            @endif
                                        @endfor
                                    </div>
                                    <span class="badge bg-primary">{{ number_format($evaluation->rating, 1) }}/5</span>
                                </div>
                            </div>
                            <p class="text-muted mb-2">
                                <i class="far fa-calendar-alt me-2"></i>
                                {{ \Carbon\Carbon::parse($evaluation->evaluation_date)->format('d/m/Y') }}
                            </p>
                            @if($evaluation->comment)
                                <p class="mb-0">{{ $evaluation->comment }}</p>
                            @endif
                        </div>
                        @endforeach

                        {{ $evaluations->links() }}
                    @else
                        <div class="alert alert-info">
                            Aucune évaluation pour le moment.
                        </div>
                    @endif
                </div>
            </div>

            <div class="mt-4 text-center">
                <a href="{{ route('dashboard.provider') }}" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i>Retour au tableau de bord
                </a>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .card {
        border-radius: 10px;
    }
    .badge {
        border-radius: 20px;
        padding: 8px 12px;
    }
</style>
@endpush
@endsection
