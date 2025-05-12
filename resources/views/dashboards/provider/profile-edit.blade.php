@extends('layouts.admin')

@section('title', 'Modifier mon profil')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-md-3">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    Menu
                </div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('dashboard.provider') }}" class="list-group-item list-group-item-action">Tableau de bord</a>
                    <a href="{{ route('profile.index') }}" class="list-group-item list-group-item-action active">Profil</a>
                    <a href="{{ route('provider.assignments.index') }}" class="list-group-item list-group-item-action">Missions</a>
                    <a href="#" class="list-group-item list-group-item-action">Disponibilités</a>
                    <a href="#" class="list-group-item list-group-item-action">Facturation</a>
                    <!-- Autres liens spécifiques aux prestataires -->
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Modifier mon profil</h4>
                    <a href="{{ route('profile.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                </div>
                <div class="card-body">
                    <!-- Alertes et notifications -->
                    @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                    @endif

                    @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                    @endif

                    <form action="{{ route('profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="card mb-4">
                            <div class="card-header">
                                Informations générales
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="name" class="form-label">Nom/Raison sociale <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $profile['name']) }}" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $profile['email']) }}" required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="telephone" class="form-label">Téléphone <span class="text-danger">*</span></label>
                                        <input type="tel" class="form-control @error('telephone') is-invalid @enderror" id="telephone" name="telephone" value="{{ old('telephone', $profile['telephone']) }}" required>
                                        @error('telephone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="speciality" class="form-label">Spécialité <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('speciality') is-invalid @enderror" id="speciality" name="speciality" value="{{ old('speciality', $profile['speciality']) }}" required>
                                        @error('speciality')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="address" class="form-label">Adresse</label>
                                        <input type="text" class="form-control @error('address') is-invalid @enderror" id="address" name="address" value="{{ old('address', $profile['address']) }}">
                                        @error('address')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="website" class="form-label">Site web</label>
                                        <input type="url" class="form-control @error('website') is-invalid @enderror" id="website" name="website" value="{{ old('website', $profile['website']) }}">
                                        @error('website')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4">{{ old('description', $profile['description']) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Enregistrer les modifications
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
