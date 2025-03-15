@extends('layouts.app')

@section('title', 'Accueil')

@section('content')
<div class="container">
    <h1 class="mt-5">Bienvenue sur Business Care</h1>
    <p class="lead">Améliorez la qualité de vie au travail de vos collaborateurs.</p>

    @php
        try {
            // Utilisation de la classe Database
            $pdo = \App\Http\Controllers\Database::getConnection();
            echo '<div class="alert alert-success">
                    <h4>Connexion à la base de données réussie</h4>
                    <p>Host: ' . env('DB_HOST') . '</p>
                    <p>Port: ' . env('DB_PORT') . '</p>
                    <p>Database: ' . env('DB_DATABASE') . '</p>
                  </div>';
        } catch (PDOException $e) {
            echo '<div class="alert alert-danger">
                    <h4>Échec de la connexion à la base de données</h4>
                    <p>Erreur : ' . $e->getMessage() . '</p>
                    <p>Code d\'erreur : ' . $e->getCode() . '</p>
                    <hr>
                    <p>Vérifiez les paramètres suivants dans votre fichier .env :</p>
                    <ul>
                        <li>DB_HOST: ' . env('DB_HOST') . '</li>
                        <li>DB_PORT: ' . env('DB_PORT') . '</li>
                        <li>DB_DATABASE: ' . env('DB_DATABASE') . '</li>
                        <li>DB_USERNAME: ' . env('DB_USERNAME') . '</li>
                    </ul>
                  </div>';
        }
    @endphp
</div>

<div class="w-100">
    <!-- Hero Section avec fond bleu clair -->
    <section class="w-100 bg-light py-5">
        <div class="container">
            <h1 class="display-4 fw-bold text-dark mb-4">Business Care</h1>
            <p class="fs-4 text-secondary mb-4">
                Découvrez nos solutions pour améliorer la qualité de vie au travail
            </p>
            <a href="{{ route('services') }}" class="btn btn-primary btn-lg px-4 py-2">
                Découvrir nos services
            </a>
        </div>
    </section>

    <!-- Services Section -->
    <section class="container py-5">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 shadow">
                    <div class="card-body">
                        <div class="display-6 mb-3">📋</div>
                        <h3 class="card-title fw-bold">Gestion de Projets</h3>
                        <p class="card-text text-secondary">
                            Solutions complètes pour gérer vos projets efficacement.
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-100 shadow">
                    <div class="card-body">
                        <div class="display-6 mb-3">📊</div>
                        <h3 class="card-title fw-bold">Analyse de données</h3>
                        <p class="card-text text-secondary">
                            Insights des données pour une prise de décision éclairée.
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-100 shadow">
                    <div class="card-body">
                        <div class="display-6 mb-3">💬</div>
                        <h3 class="card-title fw-bold">Support Client</h3>
                        <p class="card-text text-secondary">
                            Assistance 24/7 pour répondre à tous vos besoins.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="container py-5">
        <div class="row text-center">
            <div class="col-md-3">
                <div class="display-5 fw-bold text-primary">98%</                </div>
                <div class="text-secondary">Satisfaction client</div>
            </div>
            <div class="col-md-3">
                <div class="display-5 fw-bold text-primary">24/7</div>
                <div class="text-secondary">Support disponible</div>
            </div>
            <div class="col-md-3">
                <div class="display-5 fw-bold text-primary">500+</div>
                <div class="text-secondary">Clients actifs</div>
            </div>
            <div class="col-md-3">
                <div class="display-5 fw-bold text-primary">10+</div>
                <div class="text-secondary">Expérience</div>
            </div>
        </div>
    </section>

    <!-- Why Choose Us Section -->
    <section class="container py-5 mt-5">
        <h2 class="fs-1 fw-bold text-dark mb-4">Pourquoi choisir Business Care ?</h2>
        <p class="text-secondary mb-4">
            Chez Business Care, nous nous engageons à fournir des solutions de
            gestion entreprise de premier ordre. Nos services sont conçus pour
            aider votre entreprise à atteindre ses objectifs et à prospérer dans
            un environnement concurrentiel.
        </p>
        <ul class="text-secondary">
            <li>Technologie de pointe</li>
            <li>Support client exceptionnel</li>
            <li>Solutions personnalisées</li>
        </ul>
    </section>
</div>
@endsection
