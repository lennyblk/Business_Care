@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1>Liste des Conseils</h1>
            <a href="{{ route('admin.advice.create') }}" class="btn btn-primary">Créer un Conseil</a>
        </div>
        <div class="col-md-6 text-end d-flex justify-content-end gap-2">
            <a href="{{ route('admin.advice-categories.index') }}" class="btn btn-secondary">
                Gérer les Catégories
            </a>
            <a href="{{ route('admin.advice-tags.index') }}" class="btn btn-info">
                Gérer les Tags
            </a>
            <a href="{{ route('admin.advice.scheduled') }}" class="btn btn-success">
                Voir les Conseils Programmés
            </a>
        </div>
    </div>

    <table class="table mt-3">
        <thead>
            <tr>
                <th>#</th>
                <th>Titre</th>
                <th>Catégorie</th>
                <th>Date de Publication</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($advices as $advice)
            <tr>
                <td>{{ $advice['id'] }}</td>
                <td>{{ $advice['title'] }}</td>
                <td>{{ $advice['category']['name'] ?? 'N/A' }}</td>
                <td>{{ $advice['publish_date'] }}</td>
                <td>
                    <a href="{{ route('admin.advice.edit', $advice['id']) }}" class="btn btn-warning btn-sm">Modifier</a>
                    <a href="{{ route('admin.advice.schedule', $advice['id']) }}" class="btn btn-info btn-sm">Programmer</a>
                    <form action="{{ route('admin.advice.destroy', $advice['id']) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
