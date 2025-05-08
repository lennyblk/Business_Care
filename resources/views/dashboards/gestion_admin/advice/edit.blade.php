@extends('layouts.admin')

@section('content')
<div class="container">
    <h1>Modifier le Conseil</h1>
    <form action="{{ route('admin.advice.update', $advice['id']) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
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
            <label for="publish_date" class="form-label">Date de Publication</label>
            <input type="date" class="form-control" id="publish_date" name="publish_date" value="{{ $advice['publish_date'] }}" required>
        </div>
        <div class="mb-3">
            <label for="expiration_date" class="form-label">Date d'Expiration</label>
            <input type="date" class="form-control" id="expiration_date" name="expiration_date" value="{{ $advice['expiration_date'] }}">
        </div>
        <div class="mb-3">
            <label for="media" class="form-label">Médias</label>
            <input type="file" class="form-control" id="media" name="media[]" multiple>
        </div>
        <div class="mb-3">
            <label for="tags" class="form-label">Tags</label>
            <input type="text" class="form-control" id="tags" name="tags" value="{{ implode(',', $advice['tags'] ?? []) }}">
        </div>
        <div class="mb-3">
            <label for="is_personalized" class="form-label">Personnalisé</label>
            <select class="form-select" id="is_personalized" name="is_personalized">
                <option value="0" {{ $advice['is_personalized'] == 0 ? 'selected' : '' }}>Non</option>
                <option value="1" {{ $advice['is_personalized'] == 1 ? 'selected' : '' }}>Oui</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="min_formule" class="form-label">Formule Minimum</label>
            <select class="form-select" id="min_formule" name="min_formule" required>
                <option value="Starter" {{ $advice['min_formule'] == 'Starter' ? 'selected' : '' }}>Starter</option>
                <option value="Basic" {{ $advice['min_formule'] == 'Basic' ? 'selected' : '' }}>Basic</option>
                <option value="Premium" {{ $advice['min_formule'] == 'Premium' ? 'selected' : '' }}>Premium</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Mettre à jour</button>
    </form>
</div>
@endsection
