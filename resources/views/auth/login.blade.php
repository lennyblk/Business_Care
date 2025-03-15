@extends('layouts.app')

@section('title', 'Connexion')

@section('content')
<div class="min-vh-100 bg-light d-flex">
    <!-- Partie gauche - Formulaire -->
    <div class="flex-grow-1 d-flex align-items-center justify-content-center p-4">
        <div class="bg-white p-4 rounded shadow-sm w-100" style="max-width: 450px;">
            <h1 class="fs-3 fw-bold mb-4 text-dark">Connexion</h1>

            <form action="{{ route('login') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="user_type" class="form-label">Type utilisateur</label>
                    <select id="user_type" name="user_type" class="form-control" required>
                        <option value="societe">Société</option>
                        <option value="employe">Employé</option>
                        <option value="prestataire">Prestataire</option>
                        <option value="admin">Administrateur</option>
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

                <div class="mb-3 company-field">
                    <label for="company_name" class="form-label">Nom de la société</label>
                    <input type="text" id="company_name" name="company_name" class="form-control @error('company_name') is-invalid @enderror">
                    @error('company_name')
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

                <button type="submit" class="btn btn-primary w-100 py-2">Se connecter</button>
            </form>
        </div>
    </div>

    <!-- Partie droite - Logo et texte -->
    <div class="flex-grow-1 bg-primary bg-opacity-10 d-none d-lg-flex flex-column align-items-center justify-content-center p-4">
        <div class="mb-4">
            <img src="{{ asset('images/logo.svg') }}" alt="Logo" class="img-fluid" style="max-width: 150px;">
        </div>
        <h2 class="fs-2 fw-bold text-dark mb-3 text-center">Vous revoilà !</h2>
        <p class="text-secondary text-center" style="max-width: 400px;">
            Votre solution de gestion entreprise intelligente pour améliorer la
            qualité de vie au travail
        </p>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const userTypeSelect = document.getElementById('user_type');
        const companyField = document.querySelector('.company-field');

        function toggleFields() {
            const userType = userTypeSelect.value;
            
            // Gestion du champ société
            if (userType === 'societe' || userType === 'employe') {
                companyField.style.display = 'block';
                companyField.querySelector('input').required = true;
            } else {
                companyField.style.display = 'none';
                companyField.querySelector('input').required = false;
            }
        }

        toggleFields(); // État initial
        userTypeSelect.addEventListener('change', toggleFields);
    });
</script>
@endpush

@endsection
