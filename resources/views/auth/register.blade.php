@extends('layouts.app')

@section('title', 'Inscription')

@section('content')
<div class="min-vh-100 bg-light d-flex">
    <!-- Partie gauche - Logo et texte -->
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

    <!-- Partie droite - Formulaire -->
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

                <div class="company-fields">
                    <div class="mb-3">
                        <label for="company_name" class="form-label">Nom de la société</label>
                        <input type="text" id="company_name" name="company_name" class="form-control @error('company_name') is-invalid @enderror">
                        @error('company_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Adresse</label>
                        <input type="text" id="address" name="address" class="form-control @error('address') is-invalid @enderror">
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Téléphone</label>
                        <input type="tel" id="phone" name="phone" class="form-control @error('phone') is-invalid @enderror">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
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

        function toggleCompanyFields() {
            if (userTypeSelect.value === 'societe') {
                companyFields.style.display = 'block';
            } else {
                companyFields.style.display = 'none';
            }
        }

        toggleCompanyFields(); // Initial state
        userTypeSelect.addEventListener('change', toggleCompanyFields);
    });
</script>
@endpush

@endsection
