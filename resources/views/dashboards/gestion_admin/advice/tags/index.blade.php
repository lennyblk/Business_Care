@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="card mb-4">
        <div class="card-header">
            <h4>Nouveau Tag</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.advice-tags.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="name" class="form-label">Nom du tag</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <button type="submit" class="btn btn-primary">Créer</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h4>Liste des Tags</h4>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Nombre de conseils</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tags as $tag)
                    <tr>
                        <td>{{ $tag->name }}</td>
                        <td>{{ $tag->advices_count }}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal{{ $tag->id }}">
                                Modifier
                            </button>
                            <form action="{{ route('admin.advice-tags.destroy', $tag->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce tag ?')">
                                    Supprimer
                                </button>
                            </form>
                        </td>
                    </tr>

                    <!-- Modal de modification -->
                    <div class="modal fade" id="editModal{{ $tag->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Modifier le tag</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form action="{{ route('admin.advice-tags.update', $tag->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Nom</label>
                                            <input type="text" class="form-control" name="name" value="{{ $tag->name }}" required>
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
@endsection
