@extends('layouts.app')

@section('title', 'Modifier le devis')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Modifier le devis</h1>
        <a href="{{ route('quotes.show', $quote->id) }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Retour aux détails
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
            <h6 class="m-0 font-weight-bold text-primary">Modifier les informations du devis</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('quotes.update', $quote->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="formule_abonnement">Formule d'abonnement <span class="text-danger">*</span></label>
                            <select name="formule_abonnement" id="formule_abonnement" class="form-control @error('formule_abonnement') is-invalid @enderror" required>
                                <option value="">Sélectionnez une formule</option>
                                <option value="Starter" data-price="180" data-max="30" {{ $quote->formule_abonnement == 'Starter' ? 'selected' : '' }}>
                                    Starter (jusqu'à 30 salariés)
                                </option>
                                <option value="Basic" data-price="150" data-max="250" {{ $quote->formule_abonnement == 'Basic' ? 'selected' : '' }}>
                                    Basic (jusqu'à 250 salariés)
                                </option>
                                <option value="Premium" data-price="100" data-min="251" {{ $quote->formule_abonnement == 'Premium' ? 'selected' : '' }}>
                                    Premium (251+ salariés)
                                </option>
                            </select>
                            @error('formule_abonnement')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="company_size">Effectif de l'entreprise <span class="text-danger">*</span></label>
                            <input type="number" name="company_size" id="company_size" class="form-control @error('company_size') is-invalid @enderror" min="1" value="{{ old('company_size', $quote->company_size) }}" required>
                            <div id="company_size_feedback" class="text-muted small mt-1"></div>
                            @error('company_size')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="activities_count">Nombre d'activités</label>
                            <input type="number" name="activities_count" id="activities_count" class="form-control" readonly value="{{ $quote->activities_count }}">
                            <small class="form-text text-muted">Le nombre d'activités est défini par la formule choisie.</small>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="medical_appointments">Nombre de RDV médicaux</label>
                            <input type="number" name="medical_appointments" id="medical_appointments" class="form-control" readonly value="{{ $quote->medical_appointments }}">
                            <small class="form-text text-muted">Le nombre de RDV médicaux est défini par la formule choisie.</small>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="extra_appointment_fee">Tarif RDV supplémentaire</label>
                            <div class="input-group">
                                <input type="text" name="extra_appointment_fee" id="extra_appointment_fee" class="form-control" readonly value="{{ $quote->extra_appointment_fee }}">
                                <div class="input-group-append">
                                    <span class="input-group-text">€</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="chatbot_questions">Questions chatbot</label>
                            <input type="text" name="chatbot_questions" id="chatbot_questions" class="form-control" readonly value="{{ $quote->chatbot_questions }}">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="weekly_advice">Conseils hebdomadaires</label>
                            <input type="text" name="weekly_advice" id="weekly_advice" class="form-control" readonly value="{{ $quote->weekly_advice ? 'Oui' : 'Non' }}">
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="personalized_advice">Conseils personnalisés</label>
                            <input type="text" name="personalized_advice" id="personalized_advice" class="form-control" readonly value="{{ $quote->personalized_advice ? 'Oui' : 'Non' }}">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="services_details">Description complémentaire</label>
                    <textarea name="services_details" id="services_details" rows="4" class="form-control @error('services_details') is-invalid @enderror">{{ old('services_details', $quote->services_details) }}</textarea>
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
                                <p><strong>Formule:</strong> <span id="recap_formule">{{ $quote->formule_abonnement }}</span></p>
                                <p><strong>Nombre de salariés:</strong> <span id="recap_employees">{{ $quote->company_size }}</span></p>
                                <p><strong>Prix par salarié:</strong> <span id="recap_price_per_employee">
                                    @if($quote->formule_abonnement == 'Starter')
                                        180
                                    @elseif($quote->formule_abonnement == 'Basic')
                                        150
                                    @elseif($quote->formule_abonnement == 'Premium')
                                        100
                                    @else
                                        0
                                    @endif
                                </span> €</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Total Hors Taxes:</strong> <span id="recap_total_ht">{{ number_format($quote->total_amount, 2, ',', ' ') }}</span> €</p>
                                <p><strong>TVA (20%):</strong> <span id="recap_tva">{{ number_format($quote->total_amount * 0.2, 2, ',', ' ') }}</span> €</p>
                                <p class="h5 text-primary"><strong>Total TTC:</strong> <span id="recap_total_ttc">{{ number_format($quote->total_amount * 1.2, 2, ',', ' ') }}</span> €</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <input type="hidden" name="price_per_employee" id="price_per_employee" value="@if($quote->formule_abonnement == 'Starter')180@elseif($quote->formule_abonnement == 'Basic')150@elseif($quote->formule_abonnement == 'Premium')100@else0@endif">
                <input type="hidden" name="total_amount" id="total_amount" value="{{ $quote->total_amount }}">
                
                <div class="text-right">
                    <a href="{{ route('quotes.show', $quote->id) }}" class="btn btn-secondary">Annuler</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Mettre à jour le devis
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
        // Fonction pour mettre à jour les informations de la formule
        function updateFormulaInfo() {
            const formula = $('#formule_abonnement').val();
            const companySize = parseInt($('#company_size').val()) || 0;
            
            // Réinitialiser le feedback
            $('#company_size_feedback').text('');
            
            // Vérifier la compatibilité entre la formule et la taille de l'entreprise
            const $selectedOption = $('#formule_abonnement option:selected');
            const maxSize = $selectedOption.data('max');
            const minSize = $selectedOption.data('min');
            const pricePerEmployee = $selectedOption.data('price') || 0;
            
            // Validation de la taille de l'entreprise selon la formule
            let isValid = true;
            if (formula === 'Starter' && companySize > 30) {
                $('#company_size_feedback').text('La formule Starter est limitée à 30 salariés maximum.');
                $('#company_size_feedback').removeClass('text-muted').addClass('text-danger');
                isValid = false;
            } else if (formula === 'Basic' && companySize > 250) {
                $('#company_size_feedback').text('La formule Basic est limitée à 250 salariés maximum.');
                $('#company_size_feedback').removeClass('text-muted').addClass('text-danger');
                isValid = false;
            } else if (formula === 'Premium' && companySize < 251) {
                $('#company_size_feedback').text('La formule Premium est pour les entreprises de 251 salariés ou plus.');
                $('#company_size_feedback').removeClass('text-muted').addClass('text-danger');
                isValid = false;
            } else if (formula && companySize > 0) {
                $('#company_size_feedback').text('Taille d\'entreprise valide pour cette formule.');
                $('#company_size_feedback').removeClass('text-danger').addClass('text-success');
            }
            
            // Mettre à jour les caractéristiques de la formule
            switch (formula) {
                case 'Starter':
                    $('#activities_count').val('2');
                    $('#medical_appointments').val('1');
                    $('#extra_appointment_fee').val('75');
                    $('#chatbot_questions').val('6 questions');
                    $('#weekly_advice').val('Non');
                    $('#personalized_advice').val('Non');
                    break;
                case 'Basic':
                    $('#activities_count').val('3');
                    $('#medical_appointments').val('2');
                    $('#extra_appointment_fee').val('75');
                    $('#chatbot_questions').val('20 questions');
                    $('#weekly_advice').val('Oui');
                    $('#personalized_advice').val('Non');
                    break;
                case 'Premium':
                    $('#activities_count').val('4');
                    $('#medical_appointments').val('3');
                    $('#extra_appointment_fee').val('50');
                    $('#chatbot_questions').val('Illimité');
                    $('#weekly_advice').val('Oui');
                    $('#personalized_advice').val('Oui');
                    break;
                default:
                    $('#activities_count').val('');
                    $('#medical_appointments').val('');
                    $('#extra_appointment_fee').val('');
                    $('#chatbot_questions').val('');
                    $('#weekly_advice').val('');
                    $('#personalized_advice').val('');
            }
            
            // Mettre à jour le récapitulatif
            updateRecap(formula, companySize, pricePerEmployee, isValid);
        }
        
        // Fonction pour mettre à jour le récapitulatif
        function updateRecap(formula, companySize, pricePerEmployee, isValid) {
            $('#recap_formule').text(formula || '-');
            $('#recap_employees').text(companySize || '0');
            $('#recap_price_per_employee').text(pricePerEmployee || '0');
            
            let totalHT = 0;
            if (isValid && formula && companySize > 0 && pricePerEmployee > 0) {
                totalHT = companySize * pricePerEmployee;
            }
            
            const tva = totalHT * 0.2;
            const totalTTC = totalHT + tva;
            
            $('#recap_total_ht').text(formatPrice(totalHT));
            $('#recap_tva').text(formatPrice(tva));
            $('#recap_total_ttc').text(formatPrice(totalTTC));
            
            // Mettre à jour les champs cachés
            $('#price_per_employee').val(pricePerEmployee);
            $('#total_amount').val(totalHT);
        }
        
        // Formater les prix
        function formatPrice(price) {
            return parseFloat(price).toFixed(2).replace('.', ',');
        }
        
        // Événements pour recalculer le devis
        $('#formule_abonnement, #company_size').on('change input', updateFormulaInfo);
        
        // Calculer le devis au chargement
        updateFormulaInfo();
    });
</script>
@endsection