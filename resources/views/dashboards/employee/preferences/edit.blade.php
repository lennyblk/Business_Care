@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h2 class="mb-0 h5">Mes Préférences pour les Conseils</h2>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <form action="{{ route('employee.preferences.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <h5>Catégories préférées</h5>
                            <div class="row g-3">
                                @foreach($categories as $category)
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                            name="preferred_categories[]" 
                                            value="{{ $category->id }}"
                                            id="category_{{ $category->id }}"
                                            @if(in_array($category->id, $preferredCategories ?? [])) checked @endif>
                                        <label class="form-check-label" for="category_{{ $category->id }}">
                                            {{ $category->name }}
                                        </label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="mb-4">
                            <h5>Tags préférés</h5>
                            <select class="form-select select2-tags" name="preferred_tags[]" multiple>
                                @foreach($tags as $tag)
                                <option value="{{ $tag->id }}"
                                    @if(in_array($tag->id, $preferredTags ?? [])) selected @endif>
                                    {{ $tag->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <h5>Centres d'intérêt</h5>
                            <div id="interests-container" class="mb-2">
                                @foreach($interests ?? [] as $interest)
                                <span class="badge bg-info me-2 mb-2">
                                    {{ $interest }}
                                    <input type="hidden" name="interests[]" value="{{ $interest }}">
                                    <button type="button" class="btn-close btn-close-white btn-sm" onclick="removeInterest(this)"></button>
                                </span>
                                @endforeach
                            </div>
                            <div class="input-group">
                                <input type="text" id="newInterest" class="form-control" placeholder="Ajouter un centre d'intérêt">
                                <button type="button" class="btn btn-secondary" onclick="addInterest()">Ajouter</button>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Enregistrer mes préférences</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('.select2-tags').select2({
        placeholder: 'Sélectionnez vos tags préférés'
    });
});

function addInterest() {
    const input = document.getElementById('newInterest');
    const value = input.value.trim();
    if (value) {
        const container = document.getElementById('interests-container');
        const badge = document.createElement('span');
        badge.className = 'badge bg-info me-2 mb-2';
        badge.innerHTML = `
            ${value}
            <input type="hidden" name="interests[]" value="${value}">
            <button type="button" class="btn-close btn-close-white btn-sm" onclick="removeInterest(this)"></button>
        `;
        container.appendChild(badge);
        input.value = '';
    }
}

function removeInterest(button) {
    button.closest('.badge').remove();
}
</script>
@endpush
