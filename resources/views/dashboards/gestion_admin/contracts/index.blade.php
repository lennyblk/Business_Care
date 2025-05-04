@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Contrats en attente d'approbation</h1>

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
            <h6 class="m-0 font-weight-bold text-primary">Liste des contrats en attente</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Entreprise</th>
                            <th>Formule</th>
                            <th>Montant</th>
                            <th>Date début</th>
                            <th>Date fin</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingContracts as $contract)
                        <tr>
                            <td>{{ $contract->id }}</td>
                            <td>{{ $contract->company->name }}</td>
                            <td>{{ $contract->formule_abonnement }}</td>
                            <td>{{ number_format($contract->amount, 2, ',', ' ') }} €</td>
                            <td>{{ \Carbon\Carbon::parse($contract->start_date)->format('d/m/Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($contract->end_date)->format('d/m/Y') }}</td>
                            <td>
                                <a href="{{ route('admin.contracts.show', $contract->id) }}"
                                   class="btn btn-info btn-sm">
                                    <i class="fas fa-eye"></i> Voir
                                </a>
                                <form action="{{ route('admin.contracts.approve', $contract->id) }}"
                                      method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="fas fa-check"></i> Approuver
                                    </button>
                                </form>
                                <form action="{{ route('admin.contracts.reject', $contract->id) }}"
                                      method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-sm"
                                            onclick="return confirm('Êtes-vous sûr de vouloir rejeter ce contrat ?')">
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
