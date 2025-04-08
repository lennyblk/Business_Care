@extends('layouts.app')

@section('title', 'À propos')

@section('content')
<div class="min-vh-100 bg-light d-flex flex-column align-items-center justify-content-center py-5">
    <div class="container" style="max-width: 1200px;">
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

        <div class="row mb-5">
            <div class="col-md-6 mb-4">
                <div class="bg-white shadow rounded p-4 h-100">
                    <h2 class="fs-3 fw-bold text-primary mb-3">Notre mission</h2>
                    <p class="text-secondary">
                        Notre mission est de créer un environnement de travail plus sain et
                        plus productif en offrant des solutions innovantes et
                        personnalisées. Nous croyons que chaque entreprise mérite les
                        meilleurs outils pour réussir.
                    </p>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="bg-white shadow rounded p-4 h-100">
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

        <div class="bg-white shadow rounded p-4">
            <h2 class="fs-3 fw-bold text-primary mb-4 text-center">Notre équipe</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <img src="{{ asset('images/sarah.png') }}" alt="Sarah" class="card-img-top">
                        <div class="card-body text-center">
                            <h3 class="card-title fs-4 fw-bold text-primary">Sarah Garcia</h3>
                            <p class="card-text text-secondary">Responsable Développement</p>
                            <p class="card-text text-muted">
                                Sarah est passionnée par la création de solutions innovantes
                                qui améliorent la productivité et le bien-être au travail.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <img src="{{ asset('images/lenny.png') }}" alt="Lenny" class="card-img-top">
                        <div class="card-body text-center">
                            <h3 class="card-title fs-4 fw-bold text-primary">Lenny Blackett</h3>
                            <p class="card-text text-secondary">Responsable Réseau & Assistant Developpement</p>
                            <p class="card-text text-muted">
                                Lenny est un expert en développement et en gestion de réseau,
                                toujours prêt à relever de nouveaux défis techniques.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <img src="{{ asset('images/bart.png') }}" alt="Barthélemy" class="card-img-top">
                        <div class="card-body text-center">
                            <h3 class="card-title fs-4 fw-bold text-primary">Barthélemy Mahieux</h3>
                            <p class="card-text text-secondary">Développeur Hybride</p>
                            <p class="card-text text-muted">
                                Barthélemy est un développeur polyvalent, capable de travailler
                                sur plusieurs technologies pour répondre aux besoins des clients.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
