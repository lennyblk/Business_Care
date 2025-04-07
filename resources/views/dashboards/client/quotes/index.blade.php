@extends('layouts.app')

@section('title', 'Vos devis')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Gestion des devis</h1>
        <a href="{{ route('quotes.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Créer un devis
        </a>
    </div>

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
            <h6 class="m-0 font-weight-bold text-primary">Liste de vos devis</h6>
        </div>
        <div class="card-body">
            @if($quotes->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Référence</th>
                                <th>Date de création</th>
                                <th>Date d'expiration</th>
                                <th>Formule</th>
                                <th>Effectif</th>
                                <th>Total</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($quotes as $quote)
                                <tr>
                                    <td>{{ $quote->reference_number ?? 'DEVIS-' . $quote->id }}</td>
                                    <td>{{ \Carbon\Carbon::parse($quote->creation_date)->format('d/m/Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($quote->expiration_date)->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="badge badge-pill 
                                            @if($quote->formule_abonnement == 'Starter') badge-secondary
                                            @elseif($quote->formule_abonnement == 'Basic') badge-primary
                                            @elseif($quote->formule_abonnement == 'Premium') badge-warning
                                            @endif">
                                            {{ $quote->formule_abonnement }}
                                        </span>
                                    </td>
                                    <td>{{ $quote->company_size }} salariés</td>
                                    <td>{{ number_format($quote->total_amount, 2, ',', ' ') }} €</td>
                                    <td>
                                        @if($quote->status == 'Pending')
                                            <span class="badge badge-warning">En attente</span>
                                        @elseif($quote->status == 'Accepted')
                                            <span class="badge badge-success">Accepté</span>
                                        @elseif($quote->status == 'Rejected')
                                            <span class="badge badge-danger">Rejeté</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('quotes.show', $quote->id) }}" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($quote->status == 'Pending')
                                                <a href="{{ route('quotes.edit', $quote->id) }}" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('quotes.destroy', $quote->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce devis ?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-4">
                    {{ $quotes->links() }}
                </div>
            @else
                <div class="text-center py-4">
                    <p class="text-muted">Vous n'avez pas encore de devis.</p>
                    <a href="{{ route('quotes.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus mr-1"></i> Créer votre premier devis
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#dataTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/French.json"
            },
            "order": [[1, 'desc']]
        });
    });
</script>
@endsection