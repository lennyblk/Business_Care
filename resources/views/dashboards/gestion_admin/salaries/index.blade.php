@extends('layouts.admin')

@section('content')
<div class="container">
    <h1 class="mb-5">Gestion des salariés</h1>
    <button onclick="window.location='{{ route('admin.salaries.create') }}'" class="btn btn-primary mb-3">Ajouter un salarié</button>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Email</th>
                <th>Poste</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($employees as $employee)
            <tr>
                <td>{{ $employee->last_name }}</td>
                <td>{{ $employee->first_name }}</td>
                <td>{{ $employee->email }}</td>
                <td>{{ $employee->position }}</td>
                <td>
                    <button onclick="window.location='{{ route('admin.salaries.show', $employee->id) }}'" class="btn btn-info">Détails</button>
                    <button onclick="window.location='{{ route('admin.salaries.edit', $employee->id) }}'" class="btn btn-warning">Modifier</button>
                    <form action="{{ route('admin.salaries.destroy', $employee->id) }}" method="POST" style="display:inline;">
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
