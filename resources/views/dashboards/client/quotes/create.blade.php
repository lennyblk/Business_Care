@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4">Nouveau devis</h1>

    <div class="card shadow">
        <div class="card-body">
            <form method="POST" action="{{ route('quotes.store') }}" id="quoteForm">
                @csrf
                
                <!-- Champs cachés nécessaires -->
                <input type="hidden" name="formule_abonnement" id="formule_abonnement">
                <input type="hidden" name="price_per_employee" id="price_per_employee">
                <input type="hidden" name="total_amount" id="total_amount">
                
                <!-- Étape 1: Formule -->
                <div class="row mb-4">
                    <div class="col-12">
                        <label class="h5 mb-3">1. Formule d'abonnement</label>
                        <div class="btn-group btn-group-lg d-flex">
                            <input type="radio" name="formule" id="starter" value="Starter" class="btn-check" autocomplete="off">
                            <label class="btn btn-outline-secondary flex-fill" for="starter" onclick="selectFormula('Starter', 180)">
                                Starter<br><small>180€/salarié</small>
                            </label>

                            <input type="radio" name="formule" id="basic" value="Basic" class="btn-check" autocomplete="off">
                            <label class="btn btn-outline-primary flex-fill" for="basic" onclick="selectFormula('Basic', 150)">
                                Basic<br><small>150€/salarié</small>
                            </label>

                            <input type="radio" name="formule" id="premium" value="Premium" class="btn-check" autocomplete="off">
                            <label class="btn btn-outline-warning flex-fill" for="premium" onclick="selectFormula('Premium', 100)">
                                Premium<br><small>100€/salarié</small>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Étape 2: Informations -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label>2. Nombre de salariés</label>
                        <input type="number" name="company_size" id="company_size" class="form-control form-control-lg" required min="1">
                        <small class="text-danger" id="size-error"></small>
                    </div>
                    <div class="col-md-4">
                        <label>Nom de l'entreprise</label>
                        <input type="text" name="company_name" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label>Durée du contrat</label>
                        <select name="contract_duration" id="duration" class="form-control">
                            <option value="1">1 an</option>
                            <option value="2">2 ans</option>
                            <option value="3">3 ans</option>
                        </select>
                    </div>
                </div>

                <!-- Récapitulatif -->
                <div class="card bg-light">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Récapitulatif</h5>
                                <p>Formule: <strong><span id="recap-formula">-</span></strong></p>
                                <p>Effectif: <strong><span id="recap-size">0</span></strong></p>
                                <p>Prix/salarié: <strong><span id="recap-price">0</span> €</strong></p>
                            </div>
                            <div class="col-md-6 text-right">
                                <p>Total HT: <strong><span id="recap-ht">0</span> €</strong></p>
                                <p>TVA (20%): <span id="recap-tva">0</span> €</p>
                                <h4 class="text-primary">Total TTC: <span id="recap-ttc">0</span> €</h4>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-right mt-4">
                    <button type="submit" class="btn btn-primary btn-lg" id="submit-btn" disabled>
                        Créer le devis
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let selectedFormula = '';
let selectedPrice = 0;

function selectFormula(formula, price) {
    selectedFormula = formula;
    selectedPrice = price;
    
    document.getElementById('formule_abonnement').value = formula;
    document.getElementById('price_per_employee').value = price;
    
    document.getElementById('recap-formula').textContent = formula;
    document.getElementById('recap-price').textContent = price;
    
    calculateTotal();
}

function calculateTotal() {
    if (!selectedFormula) return;
    
    const size = parseInt(document.getElementById('company_size').value) || 0;
    const duration = parseInt(document.getElementById('duration').value) || 1;
    
    let isValid = true;
    const error = document.getElementById('size-error');
    
    if (selectedFormula === 'Starter' && size > 30) {
        error.textContent = 'Maximum 30 salariés pour Starter';
        isValid = false;
    } else if (selectedFormula === 'Basic' && (size < 31 || size > 250)) {
        error.textContent = 'Entre 31 et 250 salariés pour Basic';
        isValid = false;
    } else if (selectedFormula === 'Premium' && size < 251) {
        error.textContent = 'Minimum 251 salariés pour Premium';
        isValid = false;
    } else {
        error.textContent = '';
    }
    
    const totalHT = size * selectedPrice * duration;
    const tva = totalHT * 0.2;
    const totalTTC = totalHT + tva;
    
    document.getElementById('total_amount').value = totalHT;
    
    document.getElementById('recap-size').textContent = size;
    document.getElementById('recap-ht').textContent = formatPrice(totalHT);
    document.getElementById('recap-tva').textContent = formatPrice(tva);
    document.getElementById('recap-ttc').textContent = formatPrice(totalTTC);
    
    document.getElementById('submit-btn').disabled = !isValid || size === 0 || !selectedFormula;
}

function formatPrice(price) {
    return new Intl.NumberFormat('fr-FR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(price);
}

document.getElementById('company_size').addEventListener('input', calculateTotal);
document.getElementById('duration').addEventListener('change', calculateTotal);
</script>
@endsection