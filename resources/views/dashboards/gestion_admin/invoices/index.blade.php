@extends('layouts.admin')

@section('title', 'Factures')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-md-3">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    Menu
                </div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('dashboard.client') }}" class="list-group-item list-group-item-action">Tableau de bord</a>
                    <a href="#" class="list-group-item list-group-item-action">Profil</a>
                    <a href="{{ route('contracts.index') }}" class="list-group-item list-group-item-action">Contrats</a>
                    <a href="{{ route('quotes.index') }}" class="list-group-item list-group-item-action">Devis</a>
                    <a href="{{ route('employees.index') }}" class="list-group-item list-group-item-action">Collaborateurs</a>
                    <a href="{{ route('payments.index') }}" class="list-group-item list-group-item-action">Paiements</a>
                    <a href="{{ route('invoices.index') }}" class="list-group-item list-group-item-action active">Facturation</a>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Mes factures</h4>
                </div>
                <div class="card-body">
                    <!-- Alertes et notifications -->
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

                    <!-- Liste des factures -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Numéro</th>
                                    <th>Date</th>
                                    <th>Montant TTC</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(count($invoices) > 0)
                                    @foreach($invoices as $invoice)
                                    <tr>
                                        <td>{{ $invoice->invoice_number }}</td>
                                        <td>{{ \Carbon\Carbon::parse($invoice->issue_date)->format('d/m/Y') }}</td>
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
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('invoices.show', $invoice->id) }}" class="btn btn-sm btn-primary">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('invoices.download', $invoice->id) }}" class="btn btn-sm btn-secondary">
                                                    <i class="bi bi-download"></i>
                                                </a>
                                                @if($invoice->status !== 'paid')
                                                <form action="{{ route('invoices.pay', $invoice->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success">
                                                        <i class="bi bi-credit-card"></i>
                                                    </button>
                                                </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="5" class="text-center">Aucune facture trouvée</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    {{ $invoices->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
