@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="card mb-4">
        <div class="card-header">
            <h4>Nouvelle Catégorie</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.advice-categories.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="name" class="form-label">Nom</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description"></textarea>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" checked>
                    <label class="form-check-label" for="is_active">Actif</label>
                </div>
                <button type="submit" class="btn btn-primary">Créer</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h4>Liste des Catégories</h4>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Description</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categories as $category)
                    <tr>
                        <td>{{ $category->name }}</td>
                        <td>{{ $category->description }}</td>
                        <td>
                            <span class="badge {{ $category->is_active ? 'bg-success' : 'bg-danger' }}">
                                {{ $category->is_active ? 'Activée' : 'Désactivée' }}
                            </span>
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-warning" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#editModal{{ $category->id }}">
                                Modifier
                            </button>
                            <form action="{{ route('admin.advice-categories.destroy', $category->id) }}" 
                                  method="POST" class="d-inline delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-sm btn-danger delete-btn">
                                    Supprimer
                                </button>
                            </form>
                        </td>
                    </tr>

                    <!-- Modal de modification -->
                    <div class="modal fade" id="editModal{{ $category->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Modifier la catégorie</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form action="{{ route('admin.advice-categories.update', $category->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Nom</label>
                                            <input type="text" class="form-control" name="name" 
                                                   value="{{ $category->name }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="description" class="form-label">Description</label>
                                            <textarea class="form-control" name="description">{{ $category->description }}</textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label for="is_active" class="form-label">Statut</label>
                                            <select class="form-select" name="is_active" id="is_active">
                                                <option value="1" {{ $category->is_active ? 'selected' : '' }}>Activée</option>
                                                <option value="0" {{ !$category->is_active ? 'selected' : '' }}>Désactivée</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion de la suppression
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ?')) {
                this.closest('form').submit();
            }
        });
    });
});
</script>
@endpush
@endsection
