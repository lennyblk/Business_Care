@extends('layouts.app')

@section('title', 'Nos Services')

@section('content')
<div class="container mt-5">
    <h1 class="text-center mb-4">Nos Services</h1>
    <div class="row">
        <!-- Exemple de service -->
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Service 1</h5>
                    <p class="card-text">Description du service 1</p>
                    <p class="card-text"><strong>Prix:</strong> 100 €</p>
                    <p class="card-text"><strong>Durée:</strong> 60 minutes</p>
                    <a href="#" class="btn btn-primary">Voir plus</a>
                </div>
            </div>
        </div>
        <!-- Ajouter d'autres services ici -->
    </div>
</div>
@endsection
