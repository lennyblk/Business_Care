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
                <td>{{ $prestataire['last_name'] ?? 'N/A' }}</td>
                <td>{{ $prestataire['first_name'] ?? 'N/A' }}</td>
                <td>{{ $prestataire['email'] ?? 'N/A' }}</td>
                <td>{{ $prestataire['statut_prestataire'] ?? 'N/A' }}</td>
                <td>
                    <a href="{{ route('admin.prestataires.show', $prestataire['id']) }}" class="btn btn-info">Détails</a>
                    <a href="{{ route('admin.prestataires.edit', $prestataire['id']) }}" class="btn btn-warning">Modifier</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
