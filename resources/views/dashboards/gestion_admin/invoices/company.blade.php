@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Factures de {{ $company->name }}</h1>
        <div>
            <a href="{{ route('admin.company.show', $company->id) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour à l'entreprise
            </a>
            <a href="{{ route('admin.invoices.index') }}" class="btn btn-primary ml-2">
                <i class="fas fa-list"></i> Toutes les factures
            </a>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
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
                            <th>Date</th><th>Contrat</th>
                            <th>Montant HT</th>
                            <th>TVA</th>
                            <th>Montant TTC</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoices as $invoice)
                        <tr>
                            <td>{{ $invoice->invoice_number }}</td>
                            <td>{{ \Carbon\Carbon::parse($invoice->issue_date)->format('d/m/Y') }}</td>
                            <td>
                                <a href="{{ route('admin.contracts.show', $invoice->contract_id) }}">
                                    #{{ $invoice->contract_id }}
                                </a>
                            </td>
                            <td>{{ number_format($invoice->amount, 2, ',', ' ') }} €</td>
                            <td>{{ number_format($invoice->amount * 0.2, 2, ',', ' ') }} €</td>
                            <td>{{ number_format($invoice->amount * 1.2, 2, ',', ' ') }} €</td>
                            <td>
                                @if($invoice->status === 'paid')
                                    <span class="badge bg-success">Payée</span>
                                @elseif($invoice->status === 'pending')
                                    <span class="badge bg-warning">En attente</span>
                                @elseif($invoice->status === 'overdue')
                                    <span class="badge bg-danger">En retard</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('admin.invoices.show', $invoice->id) }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.invoices.download', $invoice->id) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    @if($invoice->status !== 'paid')
                                    <form action="{{ route('admin.invoices.mark-as-paid', $invoice->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Marquer cette facture comme payée?')">
                                            <i class="fas fa-check"></i>
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
            {{ $invoices->links() }}
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Résumé des factures</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total factures</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ count($invoices) }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-file-invoice fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Factures payées</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ $invoices->where('status', 'paid')->count() }}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Factures en attente</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ $invoices->whereIn('status', ['pending', 'overdue'])->count() }}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-clock fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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
