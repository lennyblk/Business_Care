@extends('layouts.app')

@section('title', 'Nos Forfaits')

@section('content')
<div class="container mt-5">
    <h1 class="text-center mb-4">Nos Forfaits Business Care</h1>
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-light text-center">
                    <h3 class="my-0 fw-bold">Starter</h3>
                </div>
                <div class="card-body">
                    <h5 class="card-price text-center">180 € <span class="text-muted fw-light">/salarié/an</span></h5>
                    <p class="text-center mb-3"><span class="badge bg-secondary">Jusqu'à 30 salariés</span></p>
                    <ul class="list-group list-group-flush mb-4">
                        <li class="list-group-item">Activités avec prestataires BC: <b>2</b></li>
                        <li class="list-group-item">RDV médicaux (présentiel/visio): <b>1</b></li>
                        <li class="list-group-item">RDV médicaux supplémentaires: <b>75€/rdv</b></li>
                        <li class="list-group-item">Accès au chatbot: <b>6 questions</b></li>
                        <li class="list-group-item">Accès aux fiches pratiques: <b>Illimité</b></li>
                        <li class="list-group-item">Conseils hebdomadaires: <b>Non</b></li>
                        <li class="list-group-item">Événements/Communautés: <b>Accès illimité</b></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow border-primary">
                <div class="card-header bg-primary text-white text-center">
                    <h3 class="my-0 fw-bold">Basic</h3>
                </div>
                <div class="card-body">
                    <h5 class="card-price text-center">150 € <span class="text-muted fw-light">/salarié/an</span></h5>
                    <p class="text-center mb-3"><span class="badge bg-primary">Jusqu'à 250 salariés</span></p>
                    <ul class="list-group list-group-flush mb-4">
                        <li class="list-group-item">Activités avec prestataires BC: <b>3</b></li>
                        <li class="list-group-item">RDV médicaux (présentiel/visio): <b>2</b></li>
                        <li class="list-group-item">RDV médicaux supplémentaires: <b>75€/rdv</b></li>
                        <li class="list-group-item">Accès au chatbot: <b>20 questions</b></li>
                        <li class="list-group-item">Accès aux fiches pratiques: <b>Illimité</b></li>
                        <li class="list-group-item">Conseils hebdomadaires: <b>Oui (non personnalisés)</b></li>
                        <li class="list-group-item">Événements/Communautés: <b>Accès illimité</b></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-dark text-white text-center">
                    <h3 class="my-0 fw-bold">Premium</h3>
                </div>
                <div class="card-body">
                    <h5 class="card-price text-center">100 € <span class="text-muted fw-light">/salarié/an</span></h5>
                    <p class="text-center mb-3"><span class="badge bg-warning text-dark">À partir de 251 salariés</span></p>
                    <ul class="list-group list-group-flush mb-4">
                        <li class="list-group-item">Activités avec prestataires BC: <b>4</b></li>
                        <li class="list-group-item">RDV médicaux (présentiel/visio): <b>3</b></li>
                        <li class="list-group-item">RDV médicaux supplémentaires: <b>50€/rdv</b></li>
                        <li class="list-group-item">Accès au chatbot: <b>Illimité</b></li>
                        <li class="list-group-item">Accès aux fiches pratiques: <b>Illimité</b></li>
                        <li class="list-group-item">Conseils hebdomadaires: <b>Oui personnalisés</b></li>
                        <li class="list-group-item">Événements/Communautés: <b>Accès illimité</b></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

</div>

<style>
    .card {
        transition: transform 0.3s;
    }
    .card:hover {
        transform: translateY(-5px);
    }
    .card-header {
        font-weight: bold;
        padding: 1rem;
    }
    .card-price {
        font-size: 1.8rem;
        margin-top: 0.5rem;
        margin-bottom: 1.5rem;
    }
    .badge {
        font-size: 0.9rem;
        padding: 0.5rem 1rem;
    }
    .list-group-item {
        display: flex;
        justify-content: space-between;
    }
    .list-group-item:before {
        content: "• ";
        color: #6c757d;
    }
</style>
@endsection
