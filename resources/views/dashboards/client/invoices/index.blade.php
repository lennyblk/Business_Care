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
                    <a href="{{ route('profile.index') }}" class="list-group-item list-group-item-action">Profil</a>
                    <a href="{{ route('contracts.index') }}" class="list-group-item list-group-item-action">Contrats</a>
                    <a href="{{ route('quotes.index') }}" class="list-group-item list-group-item-action">Devis</a>
                    <a href="{{ route('employees.index') }}" class="list-group-item list-group-item-action">Collaborateurs</a>
                    <a href="{{ route('invoices.index') }}" class="list-group-item list-group-item-action">Facturation</a>
                    <a href="{{ route('client.event_proposals.index') }}" class="list-group-item list-group-item-action">Demande d'activités</a>
                    <a href="{{ route('client.associations.index') }}" class="list-group-item list-group-item-action">Associations</a>
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
                                    <th>N° Facture</th>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Montant TTC</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(count($invoices) > 0)
                                    @foreach($invoices as $invoice)
                                    <tr>
                                        <td>{{ $invoice->invoice_number ?? 'F-'.$invoice->id }}</td>
                                        <td>{{ \Carbon\Carbon::parse($invoice->issue_date)->format('d/m/Y') }}</td>
                                        <td>
                                            @if($invoice->contract_id)
                                                <a href="{{ route('contracts.show', $invoice->contract_id) }}">
                                                    Contrat #{{ $invoice->contract_id }}
                                                </a>
                                            @else
                                                @php
                                                    $donType = "Don";
                                                    if(!empty($invoice->details)) {
                                                        $details = json_decode($invoice->details, true);
                                                        if($details && isset($details['association_name'])) {
                                                            $donType = "Don à " . $details['association_name'];
                                                        }
                                                    }
                                                @endphp
                                                <span class="badge bg-info">{{ $donType }}</span>
                                            @endif
                                        </td>
                                        <td>{{ number_format($invoice->total_amount * 1.2, 2, ',', ' ') }} €</td>
                                        <td>
                                            @if($invoice->payment_status === 'Paid' || $invoice->payment_status === 'paid')
                                                <span class="badge bg-success">Payée</span>
                                            @elseif($invoice->payment_status === 'Pending' || $invoice->payment_status === 'pending')
                                                <span class="badge bg-warning">En attente</span>
                                            @elseif($invoice->payment_status === 'Overdue' || $invoice->payment_status === 'overdue')
                                                <span class="badge bg-danger">En retard</span>
                                            @else
                                                <span class="badge bg-secondary">{{ $invoice->payment_status }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex">
                                                <a href="{{ route('invoices.show', $invoice->id) }}" class="btn btn-sm btn-primary me-1">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('invoices.download', $invoice->id) }}" class="btn btn-sm btn-secondary me-1">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                @if($invoice->payment_status !== 'Paid' && $invoice->payment_status !== 'paid')
                                                <form action="{{ route('invoices.pay', $invoice->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success">
                                                        <i class="fas fa-credit-card"></i>
                                                    </button>
                                                </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="6" class="text-center">Aucune facture trouvée</td>
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

@section('styles')
<style>
    /* Styles pour les boutons d'action */
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        line-height: 1.5;
        border-radius: 0.2rem;
    }

    .me-1 {
        margin-right: 0.25rem !important;
    }

    .d-flex {
        display: flex !important;
    }

    /* Badges */
    .badge {
        display: inline-block;
        padding: 0.35em 0.65em;
        font-size: 0.75em;
        font-weight: 700;
        line-height: 1;
        color: #fff;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border-radius: 0.25rem;
    }

    .bg-success {
        background-color: #198754 !important;
    }

    .bg-warning {
        background-color: #ffc107 !important;
        color: #000;
    }

    .bg-danger {
        background-color: #dc3545 !important;
    }

    .bg-info {
        background-color: #0dcaf0 !important;
    }
</style>
@endsection
