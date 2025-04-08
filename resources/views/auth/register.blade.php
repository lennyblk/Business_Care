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

            <form action="{{ route('register') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="user_type" class="form-label">Type d'utilisateur</label>
                    <select id="user_type" name="user_type" class="form-select @error('user_type') is-invalid @enderror">
                        <option value="societe">Société</option>
                        <option value="employe">Employé</option>
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
                        <label for="phone" class="form-label">Téléphone *</label>
                        <input type="tel" id="phone" name="phone" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="siret" class="form-label">SIRET</label>
                        <input type="text"
                               id="siret"
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

                <!-- Section employé -->
                <div class="employee-fields" style="display: none;">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="first_name" class="form-label">Prénom *</label>
                                <input type="text" id="first_name" name="first_name" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="last_name" class="form-label">Nom *</label>
                                <input type="text" id="last_name" name="last_name" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="company_name" class="form-label">Nom de l'entreprise *</label>
                        <input type="text" id="company_name" name="company_name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="position" class="form-label">Poste *</label>
                        <input type="text" id="position" name="position" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="departement" class="form-label">Département</label>
                        <input type="text" id="departement" name="departement" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label for="telephone" class="form-label">Téléphone</label>
                        <input type="tel" id="telephone" name="telephone" class="form-control">
                    </div>
                </div>

                <!-- Section prestataire -->
                <div class="provider-fields" style="display: none;">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nom *</label>
                                <input type="text" id="name" name="name" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="prenom" class="form-label">Prénom *</label>
                                <input type="text" id="prenom" name="prenom" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="specialite" class="form-label">Spécialité *</label>
                        <input type="text" id="specialite" name="specialite" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="telephone" class="form-label">Téléphone *</label>
                        <input type="tel" id="telephone" name="telephone" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="bio" class="form-label">Bio</label>
                        <textarea id="bio" name="bio" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="tarif_horaire" class="form-label">Tarif horaire (€)</label>
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
        const employeeFields = document.querySelector('.employee-fields');
        const providerFields = document.querySelector('.provider-fields');

        function toggleFields() {
            // Masquer tous les champs
            companyFields.style.display = 'none';
            employeeFields.style.display = 'none';
            providerFields.style.display = 'none';

            // Désactiver tous les champs required
            document.querySelectorAll('.company-fields input, .employee-fields input, .provider-fields input').forEach(input => {
                input.required = false;
                input.disabled = true;  // Désactiver les champs cachés
            });

            // Afficher et activer les champs appropriés
            switch(userTypeSelect.value) {
                case 'societe':
                    companyFields.style.display = 'block';
                    companyFields.querySelectorAll('input').forEach(input => {
                        input.required = true;
                        input.disabled = false;
                    });
                    break;
                case 'employe':
                    employeeFields.style.display = 'block';
                    employeeFields.querySelectorAll('input').forEach(input => {
                        input.required = true;
                        input.disabled = false;
                    });
                    break;
                case 'prestataire':
                    providerFields.style.display = 'block';
                    providerFields.querySelectorAll('input').forEach(input => {
                        input.required = true;
                        input.disabled = false;
                    });
                    break;
            }
        }

        toggleFields();
        userTypeSelect.addEventListener('change', toggleFields);
    });

    document.getElementById('siret').addEventListener('input', function(e) {
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
</script>
@endpush

@endsection
