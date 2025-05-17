@extends('layouts.app')

@section('title', 'Nouveau contrat')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-md-3">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    Menu
                </div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('dashboard.client') }}" class="list-group-item list-group-item-action">Tableau de bord</a>
                    <a href="{{ route('profile.index') }}" class="list-group-item list-group-item-action">Profil</a>
                    <a href="{{ route('contracts.index') }}" class="list-group-item list-group-item-action active">Contrats</a>
                    <a href="{{ route('quotes.index') }}" class="list-group-item list-group-item-action">Devis</a>
                    <a href="{{ route('employees.index') }}" class="list-group-item list-group-item-action">Collaborateurs</a>
                    <a href="{{ route('invoices.index') }}" class="list-group-item list-group-item-action">Facturation</a>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Nouveau contrat</h4>
                    <a href="{{ route('contracts.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Retour
                    </a>
                </div>
                <div class="card-body">
                    <!-- Alertes et notifications -->
                    @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <form action="{{ route('contracts.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="services" class="form-label">Services</label>
                            <select class="form-control @error('services') is-invalid @enderror" id="services" name="services" required>
                                <option value="">Sélectionnez un service</option>
                                <option value="Starter" {{ old('services') == 'Starter' ? 'selected' : '' }}>Starter</option>
                                <option value="Basic" {{ old('services') == 'Basic' ? 'selected' : '' }}>Basic</option>
                                <option value="Premium" {{ old('services') == 'Premium' ? 'selected' : '' }}>Premium</option>
                            </select>
                            <div class="form-text">Sélectionnez parmi les services proposés.</div>
                            @error('services')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="start_date" class="form-label">Date de début</label>
                                <input type="date" class="form-control @error('start_date') is-invalid @enderror" id="start_date" name="start_date" value="{{ old('start_date', date('Y-m-d')) }}" required>
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="end_date" class="form-label">Date de fin</label>
                                <input type="date" class="form-control @error('end_date') is-invalid @enderror" id="end_date" name="end_date" value="{{ old('end_date', date('Y-m-d', strtotime('+12 months'))) }}" required>
                                @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="amount" class="form-label">Montant mensuel (€)</label>
                                <div class="input-group">
                                    <input type="number" class="form-control @error('amount') is-invalid @enderror" id="amount" name="amount" value="{{ old('amount') }}" step="0.01" min="0" required>
                                    <span class="input-group-text">€</span>
                                </div>
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="payment_method" class="form-label">Méthode de paiement</label>
                                <select class="form-select @error('payment_method') is-invalid @enderror" id="payment_method" name="payment_method" required>
                                    <option value="">Sélectionnez une méthode</option>
                                    <option value="Direct Debit" {{ old('payment_method') == 'Direct Debit' ? 'selected' : '' }}>Prélèvement automatique</option>
                                    <option value="Invoice" {{ old('payment_method') == 'Invoice' ? 'selected' : '' }}>Facture mensuelle</option>
                                </select>
                                @error('payment_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Services proposés -->
                        @if(isset($services) && $services->count() > 0)
                        <div class="card mb-4">
                            <div class="card-header">
                                Services proposés
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    @foreach($services as $service)
                                    <div class="col-md-6 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input service-checkbox" type="checkbox" id="service{{ $service->id }}" data-service="{{ $service->name }}" data-price="{{ $service->price }}">
                                            <label class="form-check-label" for="service{{ $service->id }}">
                                                {{ $service->name }} - {{ number_format($service->price, 2, ',', ' ') }} € /mois
                                            </label>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endif
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="formule_abonnement" class="form-label">Formule d'abonnement</label>
                                <select class="form-select @error('formule_abonnement') is-invalid @enderror"
                                        id="formule_abonnement" name="formule_abonnement" required onchange="updateEmployeeCountLimit()">
                                    <option value="Starter" {{ $defaultFormula == 'Starter' ? 'selected' : '' }}>Starter (jusqu'à 30 employés)</option>
                                    <option value="Basic" {{ $defaultFormula == 'Basic' ? 'selected' : '' }}>Basic (jusqu'à 250 employés)</option>
                                    <option value="Premium" {{ $defaultFormula == 'Premium' ? 'selected' : '' }}>Premium (à partir de 251 employés)</option>
                                </select>
                                @error('formule_abonnement')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="employee_count" class="form-label">Nombre de collaborateurs</label>
                                <input type="number" class="form-control" id="employee_count" name="employee_count"
                                    value="{{ $employeeCount ?? 0 }}" min="1" required onchange="calculateContractAmount()">
                                <div class="form-text" id="employee_limit_text"></div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header">
                                Grille tarifaire
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered">
                                        <thead>
                                            <tr>
                                                <th></th>
                                                <th>Starter</th>
                                                <th>Basic</th>
                                                <th>Premium</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <th>Effectif de l'entreprise</th>
                                                <td>jusqu'à 30</td>
                                                <td>jusqu'à 250</td>
                                                <td>À partir de 251</td>
                                            </tr>
                                            <tr>
                                                <th>Activités (avec participation des prestataires)</th>
                                                <td>2</td>
                                                <td>3</td>
                                                <td>4</td>
                                            </tr>
                                            <tr>
                                                <th>RDV médicaux</th>
                                                <td>1</td>
                                                <td>2</td>
                                                <td>3</td>
                                            </tr>
                                            <tr>
                                                <th>Tarif annuel par salarié</th>
                                                <td>180 €</td>
                                                <td>150 €</td>
                                                <td>100 €</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="card mb-4">
                            <div class="card-header">
                                Récapitulatif
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Durée du contrat:</strong> <span id="contract-duration">12</span> mois</p>
                                        <p><strong>Montant mensuel:</strong> <span id="monthly-amount">0,00</span> €</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Total du contrat:</strong> <span id="total-amount">0,00</span> €</p>
                                        <p><strong>Méthode de paiement:</strong> <span id="payment-method-text">-</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="terms" required>
                            <label class="form-check-label" for="terms">
                                J'ai lu et j'accepte les <a href="#" target="_blank">termes et conditions</a>
                            </label>
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('contracts.index') }}" class="btn btn-secondary me-2">Annuler</a>
                            <button type="submit" class="btn btn-primary">Créer le contrat</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Éléments du formulaire
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');
        const amountInput = document.getElementById('amount');
        const paymentMethodSelect = document.getElementById('payment_method');
        const servicesSelect = document.getElementById('services');
        const employeeCountInput = document.getElementById('employee_count');
        const formulaSelect = document.getElementById('formule_abonnement');
        const employeeLimitText = document.getElementById('employee_limit_text');

        // Éléments du récapitulatif
        const contractDuration = document.getElementById('contract-duration');
        const monthlyAmount = document.getElementById('monthly-amount');
        const totalAmount = document.getElementById('total-amount');
        const paymentMethodText = document.getElementById('payment-method-text');

        // Limites d'employés par formule
        const employeeLimits = {
            'Starter': 30,
            'Basic': 250,
            'Premium': 10000 // Valeur très élevée pour "à partir de 251"
        };

        // Minimum d'employés par formule
        const employeeMinimums = {
            'Starter': 1,
            'Basic': 31,
            'Premium': 251
        };

        // Fonction pour mettre à jour la limite du nombre d'employés
        function updateEmployeeCountLimit() {
            const formula = formulaSelect.value;
            const maxEmployees = employeeLimits[formula] || 30;
            const minEmployees = employeeMinimums[formula] || 1;

            // Définir les attributs min et max
            employeeCountInput.setAttribute('max', maxEmployees);
            employeeCountInput.setAttribute('min', minEmployees);

            // Ajuster la valeur si elle dépasse les limites
            if (parseInt(employeeCountInput.value) > maxEmployees) {
                employeeCountInput.value = maxEmployees;
            }

            if (parseInt(employeeCountInput.value) < minEmployees) {
                employeeCountInput.value = minEmployees;
            }

            // Mettre à jour le texte d'information
            if (formula === 'Premium') {
                employeeLimitText.textContent = `Minimum: ${minEmployees} employés`;
            } else {
                employeeLimitText.textContent = `Limite: ${minEmployees} à ${maxEmployees} employés`;
            }

            // Recalculer le montant du contrat
            calculateContractAmount();
        }

        // Calculs du montant en fonction du nombre d'employés et de la formule
        function calculateContractAmount() {
            const employeeCount = parseInt(employeeCountInput.value) || 0;
            const formula = formulaSelect.value;
            let pricePerEmployee = 0;

            // Déterminer le prix par employé selon la formule
            if (formula === 'Starter') {
                pricePerEmployee = 180;
            } else if (formula === 'Basic') {
                pricePerEmployee = 150;
            } else if (formula === 'Premium') {
                pricePerEmployee = 100;
            }

            // montant total
            const totalPrice = pricePerEmployee * employeeCount;

            // Mettre à jour le champ montant
            amountInput.value = totalPrice.toFixed(2);

            // Mettre à jour le récapitulatif
            updateSummary();
        }

        // Fonction de mise à jour du récapitulatif
        function updateSummary() {
            // Calcul de la durée en mois
            const startDate = new Date(startDateInput.value);
            const endDate = new Date(endDateInput.value);

            // Vérifier que les dates sont valides
            if (isNaN(startDate.getTime()) || isNaN(endDate.getTime())) {
                return;
            }

            const diffTime = Math.abs(endDate - startDate);
            const diffMonths = Math.ceil(diffTime / (1000 * 60 * 60 * 24 * 30.44)); // Approximation

            // Mise à jour de la durée
            contractDuration.textContent = diffMonths;

            // Mise à jour du montant mensuel
            const amount = parseFloat(amountInput.value) || 0;
            monthlyAmount.textContent = amount.toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            // Mise à jour du montant total
            const total = amount * diffMonths;
            totalAmount.textContent = total.toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            // Mise à jour de la méthode de paiement
            const paymentMethod = paymentMethodSelect.options[paymentMethodSelect.selectedIndex]?.text || '-';
            paymentMethodText.textContent = paymentMethod;
        }

        // Écouteurs d'événements
        startDateInput.addEventListener('change', updateSummary);
        endDateInput.addEventListener('change', updateSummary);
        amountInput.addEventListener('input', updateSummary);
        paymentMethodSelect.addEventListener('change', updateSummary);
        employeeCountInput.addEventListener('input', calculateContractAmount);
        formulaSelect.addEventListener('change', updateEmployeeCountLimit);

        // Initialisation du calcul et du récapitulatif
        updateEmployeeCountLimit();
    });
</script>
@endpush
