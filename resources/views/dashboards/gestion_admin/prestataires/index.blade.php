@extends('layouts.admin')

@section('content')
<div class="container">
    <h1 class="mb-5">Gestion des prestataires</h1>
    <a href="{{ route('admin.prestataires.create') }}" class="btn btn-primary">Ajouter un prestataire</a>
    <table class="table">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Email</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($prestataires as $prestataire)
            <tr>
                <td>{{ $prestataire->last_name }}</td>
                <td>{{ $prestataire->first_name }}</td>
                <td>{{ $prestataire->email }}</td>
                <td>{{ $prestataire->statut_prestataire }}</td>
                <td>
                    <a href="{{ route('admin.prestataires.show', $prestataire->id) }}" class="btn btn-info">Détails</a>
                    <a href="{{ route('admin.prestataires.edit', $prestataire->id) }}" class="btn btn-warning">Modifier</a>
                    <form action="{{ route('admin.prestataires.destroy', $prestataire->id) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Supprimer</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
