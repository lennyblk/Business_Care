@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Demandes d'inscription en attente</h1>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Liste des demandes</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Type</th>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Date de demande</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingRegistrations as $registration)
                        <tr>
                            <td>{{ $registration->id }}</td>
                            <td>
                                @if($registration->user_type == 'societe')
                                    Société
                                @elseif($registration->user_type == 'employe')
                                    Employé
                                @elseif($registration->user_type == 'prestataire')
                                    Prestataire
                                @endif
                            </td>
                            <td>
                                @if($registration->user_type == 'societe')
                                    {{ $registration->company_name }}
                                @else
                                    {{ $registration->first_name }} {{ $registration->last_name }}
                                @endif
                            </td>
                            <td>{{ $registration->email }}</td>
                            <td>{{ $registration->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                            <a href="{{ route('admin.inscriptions.show', $registration->id) }}" class="btn btn-info btn-sm">
                                <i class="fas fa-eye"></i> Voir
                            </a>
                            <form action="{{ route('admin.inscriptions.approve', $registration->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm">
                                    <i class="fas fa-check"></i> Approuver
                                </button>
                            </form>
                            <form action="{{ route('admin.inscriptions.reject', $registration->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fas fa-times"></i> Rejeter
                                </button>
                            </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
