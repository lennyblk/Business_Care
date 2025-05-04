@extends('layouts.admin')

@section('title', 'Demande de changement de contrat')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h4>Demande de changement de contrat</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <p>Votre contrat actuel : <strong>{{ $contract->formule_abonnement }}</strong></p>
                        <p>Nombre d'employés : <strong>{{ $employeeCount }}</strong></p>
                        <p>Formule recommandée : <strong>{{ $recommendedFormula }}</strong></p>
                    </div>

                    <form action="{{ route('contracts.submit-change', $contract->id) }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="new_formula" class="form-label">Nouvelle formule</label>
                            <select class="form-select" id="new_formula" name="new_formula" required>
                                <option value="Starter" {{ $recommendedFormula == 'Starter' ? 'selected' : '' }}>Starter</option>
                                <option value="Basic" {{ $recommendedFormula == 'Basic' ? 'selected' : '' }}>Basic</option>
                                <option value="Premium" {{ $recommendedFormula == 'Premium' ? 'selected' : '' }}>Premium</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="reason" class="form-label">Raison du changement</label>
                            <textarea class="form-control" id="reason" name="reason" rows="3" required></textarea>
                        </div>

                        <div class="alert alert-warning">
                            <strong>Note :</strong> Votre contrat actuel restera actif jusqu'à son terme.
                            Le nouveau contrat prendra effet automatiquement à la fin du contrat actuel.
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('contracts.show', $contract->id) }}" class="btn btn-secondary me-2">Annuler</a>
                            <button type="submit" class="btn btn-primary">Soumettre la demande</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
