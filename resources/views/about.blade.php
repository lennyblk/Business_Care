@extends('layouts.app')

@section('title', 'À propos')

@section('content')
<div class="min-vh-100 bg-light d-flex flex-column align-items-center justify-content-center py-5">
    <div class="container" style="max-width: 800px;">
        <div class="text-center mb-5">
            <img src="{{ asset('images/logo.svg') }}" alt="Logo" class="img-fluid mb-4" style="max-width: 100px;">
            <h1 class="display-5 fw-bold text-primary">À propos de nous</h1>
            <p class="fs-5 text-secondary">
                Business Care est une solution de gestion d'entreprise intelligente
                conçue pour améliorer la qualité de vie au travail. Nous nous
                engageons à fournir des outils et des services qui aident les
                entreprises à prospérer.
            </p>
        </div>

        <div class="bg-white shadow rounded p-4 mb-4">
            <h2 class="fs-3 fw-bold text-primary mb-3">Notre mission</h2>
            <p class="text-secondary mb-4">
                Notre mission est de créer un environnement de travail plus sain et
                plus productif en offrant des solutions innovantes et
                personnalisées. Nous croyons que chaque entreprise mérite les
                meilleurs outils pour réussir.
            </p>

            <h2 class="fs-3 fw-bold text-primary mb-3">Nos valeurs</h2>
            <ul class="text-secondary">
                <li class="mb-2">Innovation</li>
                <li class="mb-2">Intégrité</li>
                <li class="mb-2">Excellence</li>
                <li class="mb-2">Collaboration</li>
                <li class="mb-2">Responsabilité</li>
            </ul>
        </div>
    </div>
</div>
@endsection
