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

                <div class="employee-fields">
                    <div class="mb-3">
                        <label for="first_name" class="form-label">Prénom</label>
                        <input type="text" id="first_name" name="first_name" class="form-control @error('first_name') is-invalid @enderror">
                        @error('first_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="last_name" class="form-label">Nom</label>
                        <input type="text" id="last_name" name="last_name" class="form-control @error('last_name') is-invalid @enderror">
                        @error('last_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="position" class="form-label">Poste</label>
                        <input type="text" id="position" name="position" class="form-control @error('position') is-invalid @enderror">
                        @error('position')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="company_id" class="form-label">ID de la société</label>
                        <input type="number" id="company_id" name="company_id" class="form-control @error('company_id') is-invalid @enderror">
                        @error('company_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="provider-fields">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nom</label>
                        <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror"></textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="domains" class="form-label">Domaines</label>
                        <input type="text" id="domains" name="domains" class="form-control @error('domains') is-invalid @enderror">
                        @error('domains')
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
        const employeeFields = document.querySelector('.employee-fields');
        const providerFields = document.querySelector('.provider-fields');

        function toggleFields() {
            companyFields.style.display = 'none';
            employeeFields.style.display = 'none';
            providerFields.style.display = 'none';

            if (userTypeSelect.value === 'societe') {
                companyFields.style.display = 'block';
            } else if (userTypeSelect.value === 'employe') {
                employeeFields.style.display = 'block';
            } else if (userTypeSelect.value === 'prestataire') {
                providerFields.style.display = 'block';
            }
        }

        toggleFields(); // Initial state
        userTypeSelect.addEventListener('change', toggleFields);
    });
</script>
@endpush

@endsection
