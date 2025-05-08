@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Conseils Disponibles</h1>
    <div class="row">
        @foreach($advices as $advice)
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">{{ $advice->title }}</h5>
                    <p class="card-text">{{ Str::limit($advice->content, 100) }}</p>
                    <a href="{{ route('employee.advice.show', $advice->id) }}" class="btn btn-primary">Voir Détails</a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
