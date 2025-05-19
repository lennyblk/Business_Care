@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Gestion des factures</h1>
        <div>
            <form action="{{ route('admin.invoices.generate-monthly') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success shadow-sm" onclick="return confirm('Êtes-vous sûr de vouloir générer les factures mensuelles ?')">
                    <i class="fas fa-plus fa-sm text-white-50"></i> Générer les factures mensuelles
                </button>
            </form>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Liste des factures</h6>
        </div>
        <div class="card-body">
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

            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>N° Facture</th>
                            <th>Date</th>
                            <th>Entreprise</th>
                            <th>Contrat</th>
                            <th>Montant TTC</th>
                            <th>Statut</th>
                            <th>Type</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoices as $invoice)
                        <tr>
                            <td>{{ $invoice->invoice_number ?? 'F-' . $invoice->id }}</td>
                            <td>{{ \Carbon\Carbon::parse($invoice->issue_date)->format('d/m/Y') }}</td>
                            <td>
                                <a href="{{ route('admin.company.show', $invoice->company_id) }}">
                                    {{ $invoice->company->name }}
                                </a>
                            </td>
                            <td>
                                @if($invoice->contract_id)
                                    <a href="{{ route('admin.contracts2.show', ['id' => $invoice->contract_id]) }}">
                                        #{{ $invoice->contract_id }}
                                    </a>
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>
                                @if($invoice->is_donation)
                                    {{ number_format($invoice->total_amount, 2, ',', ' ') }} €
                                @else
                                    {{ number_format($invoice->total_amount * 1.2, 2, ',', ' ') }} €
                                @endif
                            </td>
                            <td>
                                @if($invoice->payment_status === 'Paid')
                                    <span class="badge bg-success">Payée</span>
                                @elseif($invoice->payment_status === 'Pending')
                                    <span class="badge bg-warning">En attente</span>
                                @elseif($invoice->payment_status === 'Overdue')
                                    <span class="badge bg-danger">En retard</span>
                                @endif
                            </td>
                            <td>
                                @if($invoice->is_donation)
                                    <span class="badge bg-info">Don</span>
                                @else
                                    <span class="badge bg-secondary">Facture</span>
                                @endif
                            </td>
                            <td>
                            <div class="d-flex">
                                <!-- Bouton Voir -->
                                <a href="{{ route('admin.invoices.show', $invoice->id) }}" class="btn btn-primary btn-sm me-1">
                                    <i class="fas fa-eye">Détails</i>
                                </a>

                                <!-- Bouton Télécharger -->
                                <a href="{{ route('admin.invoices.download', $invoice->id) }}" class="btn btn-info btn-sm me-1">
                                    <i class="fas fa-download">Télécharger</i>
                                </a>

                                <!-- Bouton Marquer comme payé -->
                                @if($invoice->payment_status !== 'Paid')
                                <form action="{{ route('admin.invoices.mark-as-paid', $invoice->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Marquer cette facture comme payée?')">
                                        <i class="fas fa-check">Marquer comme payée</i>
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

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $invoices->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#dataTable').DataTable({
            "order": [[ 1, "desc" ]],
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/French.json"
            }
        });
    });
</script>
@endsection

@section('styles')
<style>
    /* Badges */
    .badge {
        font-size: 0.9rem;
        padding: 0.5rem 0.75rem;
        border-radius: 0.2rem;
    }

    /* Styles pour la pagination */
    .pagination {
        margin-bottom: 0;
        font-size: 0.85rem;
    }
    
    .page-item {
        margin: 0 1px;
    }

    .page-link {
        padding: 0.25rem 0.5rem;
        min-width: 32px;
        text-align: center;
    }

    .page-item:first-child .page-link,
    .page-item:last-child .page-link {
        font-size: 0.8rem;
    }
</style>
@endsection
