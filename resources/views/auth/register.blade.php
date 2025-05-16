@extends('layouts.app')

@section('title', 'Inscription')

@section('content')
<div class="min-vh-100 bg-light d-flex">
    <div class="flex-grow-1 bg-primary bg-opacity-10 d-none d-lg-flex flex-column align-items-center justify-content-center p-4">
        <div class="mb-4">
            <img src="{{ asset('images/logo.svg') }}" alt="Logo" class="img-fluid" style="max-width: 150px;">
        </div>
        <h2 class="fs-2 fw-bold text-dark mb-3 text-center">Business Care</h2>
        <p class="text-secondary text-center" style="max-width: 400px;">
            Rejoignez notre plateforme et améliorez la qualité de vie au travail
            de vos collaborateurs
        </p>
    </div>

    <div class="flex-grow-1 d-flex align-items-center justify-content-center p-4">
        <div class="bg-white p-4 rounded shadow-sm w-100" style="max-width: 450px;">
            <h1 class="fs-3 fw-bold mb-4 text-dark">Inscription</h1>

            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('register.pending') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="user_type" class="form-label">Type d'utilisateur</label>
                    <select id="user_type" name="user_type" class="form-select @error('user_type') is-invalid @enderror" required>
                        <option value="societe" selected>Société</option>
                        <option value="prestataire">Prestataire</option>
                    </select>
                    @error('user_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Mot de passe</label>
                    <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">Confirmer le mot de passe</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
                </div>

                <!-- Section société -->
                <div class="company-fields">
                    <div class="mb-3">
                        <label for="company_name" class="form-label">Nom de la société *</label>
                        <input type="text" id="company_name" name="company_name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Adresse *</label>
                        <input type="text" id="address" name="address" class="form-control" required>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="code_postal" class="form-label">Code postal *</label>
                                <input type="text" id="code_postal" name="code_postal" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="ville" class="form-label">Ville *</label>
                                <input type="text" id="ville" name="ville" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="telephone" class="form-label">Téléphone *</label>
                        <input type="tel" id="telephone" name="telephone" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="siret_company" class="form-label">SIRET</label>
                        <input type="text"
                               id="siret_company"
                               name="siret"
                               class="form-control @error('siret') is-invalid @enderror"
                               pattern="[0-9]{14}"
                               minlength="14"
                               maxlength="14"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                               title="Le numéro SIRET doit contenir exactement 14 chiffres">
                        @error('siret')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Section prestataire -->
                <div class="provider-fields" style="display: none;">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nom *</label>
                                <input type="text" id="name" name="name" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="prenom" class="form-label">Prénom *</label>
                                <input type="text" id="prenom" name="prenom" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="specialite" class="form-label">Spécialité *</label>
                        <input type="text" id="specialite" name="specialite" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label for="telephone_provider" class="form-label">Téléphone *</label>
                        <input type="tel" id="telephone_provider" name="telephone_provider" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label for="activity_type" class="form-label">Type d'activité *</label>
                        <select id="activity_type" name="activity_type" class="form-select">
                            <option value="">Choisissez un type d'activité</option>
                            <option value="rencontre sportive">Rencontre sportive</option>
                            <option value="conférence">Conférence</option>
                            <option value="webinar">Webinar</option>
                            <option value="yoga">Yoga</option>
                            <option value="pot">Pot</option>
                            <option value="séance d'art plastiques">Séance d'art plastiques</option>
                            <option value="session jeu vidéo">Session jeu vidéo</option>
                            <option value="autre">Autre (précisez)</option>
                        </select>
                    </div>

                    <div class="mb-3 other-activity-field" style="display: none;">
                        <label for="other_activity" class="form-label">Précisez votre activité *</label>
                        <input type="text" id="other_activity" name="other_activity" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label for="adresse" class="form-label">Adresse</label>
                        <input type="text" id="adresse" name="adresse" class="form-control">
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="code_postal_provider" class="form-label">Code postal</label>
                                <input type="text" id="code_postal_provider" name="code_postal_provider" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="ville_provider" class="form-label">Ville</label>
                                <input type="text" id="ville_provider" name="ville_provider" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="siret_provider" class="form-label">SIRET</label>
                        <input type="text" id="siret_provider" name="siret_provider" class="form-control" pattern="[0-9]{14}" minlength="14" maxlength="14">
                    </div>

                    <div class="mb-3">
                        <label for="bio" class="form-label">Description *</label>
                        <textarea id="bio" name="bio" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="tarif_horaire" class="form-label">Tarif horaire (€) *</label>
                        <input type="number" id="tarif_horaire" name="tarif_horaire" class="form-control" step="0.01">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2">S'inscrire</button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const userTypeSelect = document.getElementById('user_type');
        const companyFields = document.querySelector('.company-fields');
        const providerFields = document.querySelector('.provider-fields');

        // Éléments requis pour les sociétés
        const companyRequiredFields = [
            document.getElementById('company_name'),
            document.getElementById('address'),
            document.getElementById('code_postal'),
            document.getElementById('ville'),
            document.getElementById('telephone')
        ];

        // Éléments requis pour les prestataires
        const providerRequiredFields = [
            document.getElementById('name'),
            document.getElementById('prenom'),
            document.getElementById('specialite'),
            document.getElementById('telephone_provider'),
            document.getElementById('activity_type'),
            document.getElementById('bio'),
            document.getElementById('tarif_horaire')
        ];

        function toggleFields() {
            // Masquer tous les champs
            companyFields.style.display = 'none';
            providerFields.style.display = 'none';

            // Réinitialiser les attributs required
            [...companyRequiredFields, ...providerRequiredFields].forEach(field => {
                if (field) {
                    field.removeAttribute('required');
                }
            });

            // Afficher les champs appropriés selon le type d'utilisateur
            switch(userTypeSelect.value) {
                case 'societe':
                    companyFields.style.display = 'block';
                    companyRequiredFields.forEach(field => {
                        if (field) {
                            field.setAttribute('required', 'required');
                        }
                    });
                    break;

                case 'prestataire':
                    providerFields.style.display = 'block';
                    providerRequiredFields.forEach(field => {
                        if (field) {
                            field.setAttribute('required', 'required');
                        }
                    });
                    toggleOtherActivityField();
                    break;
            }
        }

        // Nouveaux champs pour le type d'activité
        const activityTypeSelect = document.getElementById('activity_type');
        const otherActivityField = document.querySelector('.other-activity-field');
        const otherActivityInput = document.getElementById('other_activity');

        function toggleOtherActivityField() {
            if (activityTypeSelect.value === 'autre') {
                otherActivityField.style.display = 'block';
                if (otherActivityInput) {
                    otherActivityInput.setAttribute('required', 'required');
                }
            } else {
                otherActivityField.style.display = 'none';
                if (otherActivityInput) {
                    otherActivityInput.removeAttribute('required');
                }
            }
        }

        // Initialiser l'affichage
        toggleFields();

        // Ajouter les écouteurs d'événements
        userTypeSelect.addEventListener('change', toggleFields);

        if (activityTypeSelect) {
            activityTypeSelect.addEventListener('change', toggleOtherActivityField);
        }
    });

    // Validation du SIRET
    const siretInputs = document.querySelectorAll('input[name="siret"]');
    siretInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value;

            // Ne garder que les chiffres
            value = value.replace(/[^0-9]/g, '');

            // Limiter à 14 chiffres
            if (value.length > 14) {
                value = value.slice(0, 14);
            }

            e.target.value = value;

            // Validation visuelle
            if (value.length > 0 && value.length !== 14) {
                e.target.setCustomValidity('Le numéro SIRET doit contenir exactement 14 chiffres');
            } else {
                e.target.setCustomValidity('');
            }
        });
    });

    // Ajout d'une vérification du formulaire avant envoi
    document.querySelector('form').addEventListener('submit', function(e) {
        // Vérifier si on est dans le mode "société"
        if (document.getElementById('user_type').value === 'societe') {
            // Vérifier que les champs requis sont remplis
            const companyName = document.getElementById('company_name').value.trim();
            const codePostal = document.getElementById('code_postal').value.trim();
            const ville = document.getElementById('ville').value.trim();
            const telephone = document.getElementById('telephone').value.trim();

            if (!companyName || !codePostal || !ville || !telephone) {
                e.preventDefault();
                alert('Veuillez remplir tous les champs obligatoires pour l\'inscription d\'une société.');
            }
        }
    });
</script>
@endpush

@endsection
