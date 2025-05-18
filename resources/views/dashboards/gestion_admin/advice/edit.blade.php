@extends('layouts.admin')

@section('content')
<div class="container">
    <h1>Modifier le Conseil</h1>
    <form action="{{ route('admin.advice.update', $advice['id']) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <input type="hidden" name="publish_date" value="{{ $advice['publish_date'] }}">
        <div class="mb-3">
            <label for="title" class="form-label">Titre</label>
            <input type="text" class="form-control" id="title" name="title" value="{{ $advice['title'] }}" required>
        </div>
        <div class="mb-3">
            <label for="content" class="form-label">Contenu</label>
            <textarea class="form-control" id="content" name="content" rows="5" required>{{ $advice['content'] }}</textarea>
        </div>
        <div class="mb-3">
            <label for="category_id" class="form-label">Catégorie</label>
            <select class="form-select" id="category_id" name="category_id" required>
                <option value="">Sélectionnez une catégorie</option>
                @foreach($categories as $category)
                <option value="{{ $category->id }}" {{ $advice['category_id'] == $category->id ? 'selected' : '' }}>
                    {{ $category->name }}
                </option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="media" class="form-label">Médias</label>
            <input type="file" class="form-control" id="media" name="media[]" multiple>
            @if(isset($advice['media']) && count($advice['media']) > 0)
                <div class="mt-2">
                    <p>Médias existants :</p>
                    <div class="row">
                        @foreach($advice['media'] as $media)
                            <div class="col-md-3 mb-2">
                                <img src="{{ asset($media['media_url']) }}" class="img-thumbnail" alt="{{ $media['title'] }}">
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
        <div class="mb-3">
            <label for="tags" class="form-label">Tags</label>
            <select class="form-select" id="tags" name="tags[]" multiple>
                @foreach($tags as $tag)
                    <option value="{{ $tag->id }}" 
                        {{ in_array($tag->id, array_column($advice['tags'] ?? [], 'id')) ? 'selected' : '' }}>
                        {{ $tag->name }}
                    </option>
                @endforeach
            </select>
            <small class="form-text text-muted">Maintenez Ctrl (Cmd sur Mac) pour sélectionner plusieurs tags</small>
        </div>
        <div class="mb-3">
            <label for="min_formule" class="form-label">Formule Minimum</label>
            <select class="form-select" id="min_formule" name="min_formule" required>
                <option value="Basic" {{ $advice['min_formule'] == 'Basic' ? 'selected' : '' }}>Basic</option>
                <option value="Premium" {{ $advice['min_formule'] == 'Premium' ? 'selected' : '' }}>Premium</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Mettre à jour</button>
        <a href="{{ route('admin.advice.index') }}" class="btn btn-secondary">Retour</a>
    </form>
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('#tags').select2({
        placeholder: 'Sélectionnez des tags',
        width: '100%'
    });
});
</script>
@endpush
@endsection
