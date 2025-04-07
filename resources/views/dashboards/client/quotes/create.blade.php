@extends('layouts.app')

@section('title', 'Créer un devis')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Créer un nouveau devis</h1>
        <a href="{{ route('quotes.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Retour à la liste
        </a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Grille Tarifaire</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th class="text-center bg-light">Starter</th>
                                    <th class="text-center bg-light">Basic</th>
                                    <th class="text-center bg-light">Premium</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Effectif de l'entreprise</strong></td>
                                    <td class="text-center">jusqu'à 30</td>
                                    <td class="text-center">jusqu'à 250</td>
                                    <td class="text-center">À partir de 251</td>
                                </tr>
                                <tr>
                                    <td><strong>Activités</strong> (avec participation des prestataires de BC)</td>
                                    <td class="text-center">2</td>
                                    <td class="text-center">3</td>
                                    <td class="text-center">4</td>
                                </tr>
                                <tr>
                                    <td><strong>RDV médicaux</strong> (présentiel/visio)</td>
                                    <td class="text-center">1</td>
                                    <td class="text-center">2</td>
                                    <td class="text-center">3</td>
                                </tr>
                                <tr>
                                    <td><strong>RDV médicaux supplémentaires</strong> (aux frais des salariés)</td>
                                    <td class="text-center">75€/rdv</td>
                                    <td class="text-center">75€/rdv</td>
                                    <td class="text-center">50€/rdv</td>
                                </tr>
                                <tr>
                                    <td><strong>Accès au chatbot</strong></td>
                                    <td class="text-center">6 questions</td>
                                    <td class="text-center">20 questions</td>
                                    <td class="text-center">illimité</td>
                                </tr>
                                <tr>
                                    <td><strong>Accès aux fiches pratiques BC</strong></td>
                                    <td class="text-center">illimité</td>
                                    <td class="text-center">illimité</td>
                                    <td class="text-center">illimité</td>
                                </tr>
                                <tr>
                                    <td><strong>Conseils hebdomadaires</strong></td>
                                    <td class="text-center">non</td>
                                    <td class="text-center">oui (non personnalisés)</td>
                                    <td class="text-center">oui personnalisés (suggestion d'activités)</td>
                                </tr>
                                <tr>
                                    <td><strong>Événements / Communautés</strong> (sans intervention des prestataires de BC, événements internes de l'entreprise)</td>
                                    <td colspan="3" class="text-center">accès illimité</td>
                                </tr>
                                <tr class="bg-light">
                                    <td><strong>Tarif annuel par salarié</strong></td>
                                    <td class="text-center"><strong>180 €</strong></td>
                                    <td class="text-center"><strong>150 €</strong></td>
                                    <td class="text-center"><strong>100 €</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Informations du devis</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('quotes.store') }}" method="POST">
                @csrf
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="company_name">Nom de l'entreprise <span class="text-danger">*</span></label>
                            <input type="text" name="company_name" id="company_name" class="form-control @error('company_name') is-invalid @enderror" value="{{ old('company_name') }}" required>
                            @error('company_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-12">
                        <label><strong>Formule d'abonnement</strong> <span class="text-danger">*</span></label>
                        <div class="btn-group d-flex formule-buttons mb-3">
                            <button type="button" class="btn btn-outline-secondary formula-btn" data-formula="Starter">Starter</button>
                            <button type="button" class="btn btn-outline-primary formula-btn" data-formula="Basic">Basic</button>
                            <button type="button" class="btn btn-outline-warning formula-btn" data-formula="Premium">Premium</button>
                        </div>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-12">
                        <label><strong>Effectif de l'entreprise</strong> <span class="text-danger">*</span></label>
                        <div class="btn-group d-flex company-size-buttons mb-3">
                            <button type="button" class="btn btn-outline-secondary size-btn" data-min="1" data-max="30">Jusqu'à 30</button>
                            <button type="button" class="btn btn-outline-primary size-btn" data-min="31" data-max="250">De 31 à 250</button>
                            <button type="button" class="btn btn-outline-warning size-btn" data-min="251" data-max="99999">251 et plus</button>
                        </div>
                        
                        <div class="form-group">
                            <label for="company_size">Nombre exact de salariés <span class="text-danger">*</span></label>
                            <input type="number" name="company_size" id="company_size" class="form-control @error('company_size') is-invalid @enderror" min="1" value="{{ old('company_size', 1) }}" required>
                            @error('company_size')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="contract_duration">Durée du contrat (années) <span class="text-danger">*</span></label>
                            <select name="contract_duration" id="contract_duration" class="form-control @error('contract_duration') is-invalid @enderror" required>
                                <option value="1" {{ old('contract_duration') == 1 ? 'selected' : '' }}>1 an</option>
                                <option value="2" {{ old('contract_duration') == 2 ? 'selected' : '' }}>2 ans</option>
                                <option value="3" {{ old('contract_duration') == 3 ? 'selected' : '' }}>3 ans</option>
                                <option value="4" {{ old('contract_duration') == 4 ? 'selected' : '' }}>4 ans</option>
                                <option value="5" {{ old('contract_duration') == 5 ? 'selected' : '' }}>5 ans</option>
                            </select>
                            @error('contract_duration')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="services_details">Description complémentaire (optionnel)</label>
                    <textarea name="services_details" id="services_details" rows="3" class="form-control @error('services_details') is-invalid @enderror">{{ old('services_details') }}</textarea>
                    @error('services_details')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <hr>
                
                <div class="card bg-light mb-4">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold">Récapitulatif du devis</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Formule:</strong> <span id="recap_formule">-</span></p>
                                <p><strong>Nombre de salariés:</strong> <span id="recap_employees">0</span></p>
                                <p><strong>Durée du contrat:</strong> <span id="recap_duration">1</span> an(s)</p>
                                <p><strong>Prix par salarié:</strong> <span id="recap_price_per_employee">0</span> €</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Total annuel HT:</strong> <span id="recap_annual_ht">0,00</span> €</p>
                                <p><strong>Total HT sur la durée du contrat:</strong> <span id="recap_total_ht">0,00</span> €</p>
                                <p><strong>TVA (20%):</strong> <span id="recap_tva">0,00</span> €</p>
                                <p class="h5 text-primary"><strong>Total TTC:</strong> <span id="recap_total_ttc">0,00</span> €</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Champs cachés pour stocker les informations de la formule -->
                <input type="hidden" name="formule_abonnement" id="formule_abonnement" value="">
                <input type="hidden" name="price_per_employee" id="price_per_employee" value="0">
                <input type="hidden" name="activities_count" id="activities_count" value="0">
                <input type="hidden" name="medical_appointments" id="medical_appointments" value="0">
                <input type="hidden" name="extra_appointment_fee" id="extra_appointment_fee" value="0">
                <input type="hidden" name="chatbot_questions" id="chatbot_questions" value="">
                <input type="hidden" name="weekly_advice" id="weekly_advice" value="">
                <input type="hidden" name="personalized_advice" id="personalized_advice" value="">
                <input type="hidden" name="annual_amount" id="annual_amount" value="0">
                <input type="hidden" name="total_amount" id="total_amount" value="0">
                <input type="hidden" name="total_amount_ttc" id="total_amount_ttc" value="0">
                
                <div class="text-right">
                    <a href="{{ route('quotes.index') }}" class="btn btn-secondary">Annuler</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Créer le devis
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Définir les formules et leurs caractéristiques
        const formulas = {
            'Starter': {
                maxSize: 30,
                minSize: 1,
                pricePerEmployee: 180,
                activities: 2,
                medicalAppointments: 1,
                extraAppointmentFee: '75€/rdv',
                chatbotQuestions: '6 questions',
                guides: 'illimité',
                weeklyAdvice: 'non',
                personalizedAdvice: 'non',
                events: 'accès illimité',
                buttonClass: 'btn-secondary'
            },
            'Basic': {
                maxSize: 250,
                minSize: 31,
                pricePerEmployee: 150,
                activities: 3,
                medicalAppointments: 2,
                extraAppointmentFee: '75€/rdv',
                chatbotQuestions: '20 questions',
                guides: 'illimité',
                weeklyAdvice: 'oui (non personnalisés)',
                personalizedAdvice: 'non',
                events: 'accès illimité',
                buttonClass: 'btn-primary'
            },
            'Premium': {
                minSize: 251,
                pricePerEmployee: 100,
                activities: 4,
                medicalAppointments: 3,
                extraAppointmentFee: '50€/rdv',
                chatbotQuestions: 'illimité',
                guides: 'illimité',
                weeklyAdvice: 'oui',
                personalizedAdvice: 'oui personnalisés (suggestion d\'activités)',
                events: 'accès illimité',
                buttonClass: 'btn-warning'
            }
        };
        
        // Fonction pour déterminer la formule en fonction du nombre de salariés
        function determineFormula(companySize) {
            if (companySize <= 30) {
                return 'Starter';
            } else if (companySize <= 250) {
                return 'Basic';
            } else {
                return 'Premium';
            }
        }
        
        // Fonction pour mettre à jour les boutons de formule
        function updateFormulaButtons(formula) {
            $('.formula-btn').removeClass('active btn-secondary btn-primary btn-warning')
                            .addClass('btn-outline-secondary btn-outline-primary btn-outline-warning');
            
            $(`.formula-btn[data-formula="${formula}"]`)
                .removeClass(`btn-outline-${formulas[formula].buttonClass.replace('btn-', '')}`)
                .addClass(`active ${formulas[formula].buttonClass}`);
                
            $('#formule_abonnement').val(formula);
        }
        
        // Fonction pour mettre à jour les boutons de taille d'entreprise
        function updateSizeButtons(companySize) {
            $('.size-btn').removeClass('active btn-secondary btn-primary btn-warning')
                        .addClass('btn-outline-secondary btn-outline-primary btn-outline-warning');
            
            if (companySize <= 30) {
                $('.size-btn[data-max="30"]')
                    .removeClass('btn-outline-secondary')
                    .addClass('active btn-secondary');
            } else if (companySize <= 250) {
                $('.size-btn[data-max="250"]')
                    .removeClass('btn-outline-primary')
                    .addClass('active btn-primary');
            } else {
                $('.size-btn[data-max="99999"]')
                    .removeClass('btn-outline-warning')
                    .addClass('active btn-warning');
            }
        }
        
        // Fonction pour mettre à jour les informations du devis
        function updateQuoteInfo() {
            const companySize = parseInt($('#company_size').val()) || 0;
            const contractDuration = parseInt($('#contract_duration').val()) || 1;
            
            // Déterminer la formule
            const formula = determineFormula(companySize);
            const formulaInfo = formulas[formula];
            
            // Mettre à jour les boutons
            updateFormulaButtons(formula);
            updateSizeButtons(companySize);
            
            // Mettre à jour les récapitulatifs
            $('#recap_formule').text(formula);
            $('#recap_employees').text(companySize);
            $('#recap_duration').text(contractDuration);
            $('#recap_price_per_employee').text(formulaInfo.pricePerEmployee);
            
            // Calculer les montants
            const annualAmount = companySize * formulaInfo.pricePerEmployee;
            const totalAmount = annualAmount * contractDuration;
            const tva = totalAmount * 0.2;
            const totalTTC = totalAmount + tva;
            
            // Afficher les montants formatés
            $('#recap_annual_ht').text(formatPrice(annualAmount));
            $('#recap_total_ht').text(formatPrice(totalAmount));
            $('#recap_tva').text(formatPrice(tva));
            $('#recap_total_ttc').text(formatPrice(totalTTC));
            
            // Mettre à jour les champs cachés
            $('#formule_abonnement').val(formula);
            $('#price_per_employee').val(formulaInfo.pricePerEmployee);
            $('#activities_count').val(formulaInfo.activities);
            $('#medical_appointments').val(formulaInfo.medicalAppointments);
            $('#extra_appointment_fee').val(formulaInfo.extraAppointmentFee);
            $('#chatbot_questions').val(formulaInfo.chatbotQuestions);
            $('#weekly_advice').val(formulaInfo.weeklyAdvice);
            $('#personalized_advice').val(formulaInfo.personalizedAdvice);
            $('#annual_amount').val(annualAmount);
            $('#total_amount').val(totalAmount);
            $('#total_amount_ttc').val(totalTTC);
        }
        
        // Formater les prix
        function formatPrice(price) {
            return parseFloat(price).toFixed(2).replace('.', ',');
        }
        
        // Événements pour les boutons de formule
        $('.formula-btn').on('click', function() {
            const formula = $(this).data('formula');
            const formulaInfo = formulas[formula];
            
            // Sélectionner la formule
            updateFormulaButtons(formula);
            
            // Mettre à jour la taille de l'entreprise en fonction de la formule
            if (formula === 'Starter') {
                $('#company_size').val(Math.min($('#company_size').val(), 30) || 1);
            } else if (formula === 'Basic') {
                $('#company_size').val(Math.max(Math.min($('#company_size').val(), 250), 31));
            } else if (formula === 'Premium') {
                $('#company_size').val(Math.max($('#company_size').val(), 251));
            }
            
            // Mettre à jour les infos du devis
            updateQuoteInfo();
        });
        
        // Événements pour les boutons de taille d'entreprise
        $('.size-btn').on('click', function() {
            const minSize = parseInt($(this).data('min'));
            const maxSize = parseInt($(this).data('max'));
            
            // Si c'est "jusqu'à 30", on met 1 par défaut 
            // Si c'est "31 à 250", on met 31 par défaut
            // Si c'est "251 et plus", on met 251 par défaut
            let defaultSize = minSize;
            
            // Mettre à jour la taille de l'entreprise
            $('#company_size').val(defaultSize);
            
            // Mettre à jour les infos du devis
            updateQuoteInfo();
        });
        
        // Événements pour recalculer le devis
        $('#company_size, #contract_duration').on('change input', function() {
            // Si la taille de l'entreprise est modifiée manuellement, on ajuste la formule automatiquement
            updateQuoteInfo();
        });
        
        // Calculer le devis au chargement
        updateQuoteInfo();
    });
</script>
@endsection