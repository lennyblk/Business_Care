@extends('layouts.app')

@section('title', $service->title)

@section('content')
<div class="container mt-5">
    <h1 class="text-center mb-4">{{ $service->title }}</h1>
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">{{ $service->title }}</h5>
                    <p class="card-text">{{ $service->description }}</p>
                    <p class="card-text"><strong>Prix:</strong> {{ $service->price }} €</p>
                    <p class="card-text"><strong>Durée:</strong> {{ $service->duration }} minutes</p>
                    <a href="{{ route('services') }}" class="btn btn-primary">Retour aux services</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
