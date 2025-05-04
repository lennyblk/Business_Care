@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Détails du contrat #{{ $contract->id }}</h1>
        <a href="{{ route('admin.contracts.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informations de l'entreprise</h6>
                </div>
                <div class="card-body">
                    <p><strong>Nom :</strong> {{ $contract->company->name }}</p>
                    <p><strong>Email :</strong> {{ $contract->company->email }}</p>
                    <p><strong>Téléphone :</strong> {{ $contract->company->telephone }}</p>
                    <p><strong>Adresse :</strong> {{ $contract->company->address }}</p>
                    <p><strong>Ville :</strong> {{ $contract->company->ville }} {{ $contract->company->code_postal }}</p>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Détails du contrat</h6>
                </div>
                <div class="card-body">
                    <p><strong>Formule :</strong> {{ $contract->formule_abonnement }}</p>
                    <p><strong>Montant :</strong> {{ number_format($contract->amount, 2, ',', ' ') }} €</p>
                    <p><strong>Date de début :</strong> {{ \Carbon\Carbon::parse($contract->start_date)->format('d/m/Y') }}</p>
                    <p><strong>Date de fin :</strong> {{ \Carbon\Carbon::parse($contract->end_date)->format('d/m/Y') }}</p>
                    <p><strong>Méthode de paiement :</strong> {{ $contract->payment_method }}</p>
                    <p><strong>Services :</strong></p>
                    <div class="border p-3">
                        {!! nl2br(e($contract->services)) !!}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-body text-center">
                    <form action="{{ route('admin.contracts.approve', $contract->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success btn-lg mr-3">
                            <i class="fas fa-check"></i> Approuver le contrat
                        </button>
                    </form>
                    <form action="{{ route('admin.contracts.reject', $contract->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-lg"
                                onclick="return confirm('Êtes-vous sûr de vouloir rejeter ce contrat ?')">
                            <i class="fas fa-times"></i> Rejeter le contrat
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
