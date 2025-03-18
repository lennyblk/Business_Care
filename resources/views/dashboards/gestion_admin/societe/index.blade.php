@extends('layouts.admin')

@section('content')
<div class="container">
    <h1 class="mb-5">Gestion des entreprises</h1>
    <button class="btn btn-primary" onclick="window.location='{{ route('admin.company.create') }}'">Ajouter une entreprise</button>
    <table class="mt-4 table table-bordered">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Adresse</th>
                <th>Email</th>
                <th>Formule d'abonnement</th>
                <th>Statut du compte</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($companies as $company)
            <tr>
                <td>{{ $company->name }}</td>
                <td>{{ $company->address }}</td>
                <td>{{ $company->email }}</td>
                <td>{{ $company->formule_abonnement }}</td>
                <td>{{ $company->statut_compte }}</td>
                <td>
                    <button class="btn" onclick="window.location='{{ route('admin.company.show', $company->id) }}'">Détails</button>
                    <button class="btn" onclick="window.location='{{ route('admin.company.edit', $company->id) }}'">Modifier</button>
                    <form action="{{ route('admin.company.destroy', $company->id) }}" method="POST" style="display:inline;">

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
