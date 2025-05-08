@extends('layouts.admin')

@section('content')
<div class="container">
    <h1>Détails du Conseil</h1>
    <div class="mb-3">
        <strong>Titre :</strong> {{ $advice['title'] }}
    </div>
    <div class="mb-3">
        <strong>Contenu :</strong>
        <p>{{ $advice['content'] }}</p>
    </div>
    <div class="mb-3">
        <strong>Catégorie :</strong> {{ $advice['category']['name'] ?? 'N/A' }}
    </div>
    <div class="mb-3">
        <strong>Date de Publication :</strong> {{ $advice['publish_date'] }}
    </div>
    <div class="mb-3">
        <strong>Date d'Expiration :</strong> {{ $advice['expiration_date'] ?? 'N/A' }}
    </div>
    <div class="mb-3">
        <strong>Personnalisé :</strong> {{ $advice['is_personalized'] ? 'Oui' : 'Non' }}
    </div>
    <div class="mb-3">
        <strong>Formule Minimum :</strong> {{ $advice['min_formule'] }}
    </div>
    <div class="mb-3">
        <strong>Médias :</strong>
        <ul>
            @foreach($advice['media'] as $media)
            <li><a href="{{ $media['media_url'] }}" target="_blank">{{ $media['title'] }}</a></li>
            @endforeach
        </ul>
    </div>
    <div class="mb-3">
        <strong>Tags :</strong> {{ implode(', ', $advice['tags'] ?? []) }}
    </div>
    <a href="{{ route('admin.advice.edit', $advice['id']) }}" class="btn btn-warning">Modifier</a>
    <form action="{{ route('admin.advice.destroy', $advice['id']) }}" method="POST" style="display:inline;">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger">Supprimer</button>
    </form>
</div>
@endsection
