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
                    <a href="#" class="list-group-item list-group-item-action active">Tableau de bord</a>
                    <a href="{{ route('profile.index') }}" class="list-group-item list-group-item-action">Profil prestataire</a>
                    <a href="{{ route('provider.evaluations.index') }}" class="list-group-item list-group-item-action">Suivi des évaluations</a>
                    <a href="{{ route('provider.assignments.index') }}" class="list-group-item list-group-item-action">Activités</a>
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
                        <input type="hidden" name="user_type" value="provider">

                        <div class="card mb-4">
                            <div class="card-header">
                                Informations générales
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="first_name" class="form-label">Prénom <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('first_name') is-invalid @enderror"
                                               id="first_name" name="first_name" value="{{ old('first_name', $profile->first_name) }}" required>
                                        @error('first_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="last_name" class="form-label">Nom <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('last_name') is-invalid @enderror"
                                               id="last_name" name="last_name" value="{{ old('last_name', $profile->last_name) }}" required>
                                        @error('last_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                           id="email" name="email" value="{{ old('email', $profile->email) }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="telephone" class="form-label">Téléphone <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control @error('telephone') is-invalid @enderror"
                                           id="telephone" name="telephone" value="{{ old('telephone', $profile->telephone) }}" required>
                                    @error('telephone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="adresse" class="form-label">Adresse</label>
                                    <input type="text" class="form-control @error('adresse') is-invalid @enderror"
                                           id="adresse" name="adresse" value="{{ old('adresse', $profile->adresse) }}">
                                    @error('adresse')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="code_postal" class="form-label">Code postal</label>
                                    <input type="text" class="form-control @error('code_postal') is-invalid @enderror"
                                           id="code_postal" name="code_postal" value="{{ old('code_postal', $profile->code_postal) }}">
                                    @error('code_postal')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="ville" class="form-label">Ville</label>
                                    <input type="text" class="form-control @error('ville') is-invalid @enderror"
                                           id="ville" name="ville" value="{{ old('ville', $profile->ville) }}">
                                    @error('ville')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="siret" class="form-label">SIRET</label>
                                    <input type="text" class="form-control @error('siret') is-invalid @enderror"
                                           id="siret"
                                           name="siret"
                                           value="{{ old('siret', $profile->siret) }}"
                                           pattern="[0-9]{14}"
                                           minlength="14"
                                           maxlength="14"
                                           title="Le numéro SIRET doit contenir exactement 14 chiffres">
                                    @error('siret')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Le numéro SIRET doit contenir 14 chiffres. Laissez vide si vous ne souhaitez pas le modifier.
                                    </small>
                                </div>

                                <div class="mb-3">
                                    <label for="activity_type" class="form-label">Type d'activité <span class="text-danger">*</span></label>
                                    <select class="form-select @error('activity_type') is-invalid @enderror"
                                            id="activity_type" name="activity_type" required>
                                        <option value="">Sélectionnez un type d'activité</option>
                                        @php
                                            $currentType = old('activity_type', $profile->activity_type ?? '');
                                            $activityTypes = [
                                                'rencontre sportive' => 'Rencontre sportive',
                                                'conférence' => 'Conférence',
                                                'webinar' => 'Webinar',
                                                'yoga' => 'Yoga',
                                                'pot' => 'Pot',
                                                'séance d\'art plastiques' => 'Séance d\'art plastiques',
                                                'session jeu vidéo' => 'Session jeu vidéo',
                                                'autre' => 'Autre'
                                            ];
                                        @endphp
                                        @foreach($activityTypes as $value => $label)
                                            <option value="{{ $value }}" {{ $currentType == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('activity_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3 other-activity-field" style="display: {{ ($currentType == 'autre') ? 'block' : 'none' }};">
                                    <label for="other_activity" class="form-label">Précisez votre activité</label>
                                    <input type="text" class="form-control @error('other_activity') is-invalid @enderror"
                                           id="other_activity" name="other_activity"
                                           value="{{ old('other_activity', $profile->other_activity ?? '') }}">
                                    @error('other_activity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('description') is-invalid @enderror"
                                            id="description"
                                            name="description"
                                            rows="3"
                                            required>{{ old('description', $profile->description ?? '') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="domains" class="form-label">Domaines d'expertise</label>
                                    <input type="text" class="form-control @error('domains') is-invalid @enderror"
                                           id="domains"
                                           name="domains"
                                           value="{{ old('domains', $profile->domains ?? '') }}">
                                    @error('domains')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="tarif_horaire" class="form-label">Tarif horaire <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('tarif_horaire') is-invalid @enderror"
                                           id="tarif_horaire"
                                           name="tarif_horaire"
                                           step="0.01"
                                           value="{{ old('tarif_horaire', $profile->tarif_horaire ?? '') }}"
                                           required>
                                    @error('tarif_horaire')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Additional fields -->
                            </div>
                        </div>

                        <div class="d-grid gap-2">
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

@push('scripts')
<script>
    // Validation du SIRET
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
