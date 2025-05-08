@extends('layouts.admin')

@section('content')
<div class="container">
    <h1>Créer un Conseil</h1>
    <form action="{{ route('admin.advice.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label for="title" class="form-label">Titre</label>
            <input type="text" class="form-control" id="title" name="title" required>
        </div>
        <div class="mb-3">
            <label for="content" class="form-label">Contenu</label>
            <textarea class="form-control" id="content" name="content" rows="5" required></textarea>
        </div>
        <div class="mb-3">
            <label for="category_id" class="form-label">Catégorie</label>
            <select class="form-select" id="category_id" name="category_id" required>
                <option value="">Sélectionnez une catégorie</option>
                @foreach($categories as $category)
                <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="publish_date" class="form-label">Date de Publication</label>
            <input type="date" class="form-control" id="publish_date" name="publish_date" required>
        </div>
        <div class="mb-3">
            <label for="expiration_date" class="form-label">Date d'Expiration</label>
            <input type="date" class="form-control" id="expiration_date" name="expiration_date">
        </div>
        <div class="mb-3">
            <label for="media" class="form-label">Images</label>
            <input type="file" class="form-control" id="media" name="media[]" multiple accept="image/*">
        </div>
        <div class="mb-3">
            <label for="tags" class="form-label">Tags</label>
            <select class="form-select select2-tags" id="tags" name="tags[]" multiple>
                @foreach($tags as $tag)
                    <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="is_personalized" class="form-label">Personnalisé</label>
            <select class="form-select" id="is_personalized" name="is_personalized">
                <option value="0">Non</option>
                <option value="1">Oui</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="min_formule" class="form-label">Formule Minimum</label>
            <select class="form-select" id="min_formule" name="min_formule" required>
                <option value="Basic">Basic</option>
                <option value="Premium">Premium</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Créer</button>
    </form>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
.select2-container--default .select2-selection--multiple {
    min-height: 38px;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('.select2-tags').select2({
        placeholder: 'Sélectionnez des tags',
        allowClear: true
    });
});
</script>
@endpush
