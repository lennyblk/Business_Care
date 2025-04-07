@extends('layouts.app')

@section('title', 'Détails du devis')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Détails du devis</h1>
        <div>
            <a href="{{ route('quotes.index') }}" class="btn btn-sm btn-secondary shadow-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Retour à la liste
            </a>
            @if($quote->status == 'Pending')
                <a href="{{ route('quotes.edit', $quote->id) }}" class="btn btn-sm btn-primary shadow-sm">
                    <i class="fas fa-edit fa-sm text-white-50"></i> Modifier
                </a>
            @endif
        </div>
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

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        Devis #{{ $quote->reference_number ?? 'DEVIS-' . $quote->id }}
                    </h6>
                    <span class="badge badge-lg 
                        @if($quote->status == 'Pending') badge-warning
                        @elseif($quote->status == 'Accepted') badge-success
                        @elseif($quote->status == 'Rejected') badge-danger
                        @endif">
                        @if($quote->status == 'Pending') En attente
                        @elseif($quote->status == 'Accepted') Accepté
                        @elseif($quote->status == 'Rejected') Rejeté
                        @endif
                    </span>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5 class="font-weight-bold">Informations du devis</h5>
                            <p><strong>Date de création:</strong> {{ \Carbon\Carbon::parse($quote->creation_date)->format('d/m/Y') }}</p>
                            <p><strong>Date d'expiration:</strong> {{ \Carbon\Carbon::parse($quote->expiration_date)->format('d/m/Y') }}</p>
                            <p>
                                <strong>Statut:</strong> 
                                @if($quote->status == 'Pending')
                                    <span class="badge badge-warning">En attente</span>
                                @elseif($quote->status == 'Accepted')
                                    <span class="badge badge-success">Accepté</span>
                                @elseif($quote->status == 'Rejected')
                                    <span class="badge badge-danger">Rejeté</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h5 class="font-weight-bold">Votre société</h5>
                            <p><strong>Nom:</strong> {{ $quote->company->name ?? 'Votre société' }}</p>
                            <p><strong>Effectif:</strong> {{ $quote->company_size }} salariés</p>
                            <p><strong>Formule:</strong> 
                                <span class="badge badge-pill 
                                    @if($quote->formule_abonnement == 'Starter') badge-secondary
                                    @elseif($quote->formule_abonnement == 'Basic') badge-primary
                                    @elseif($quote->formule_abonnement == 'Premium') badge-warning
                                    @endif">
                                    {{ $quote->formule_abonnement }}
                                </span>
                            </p>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <h5 class="font-weight-bold mb-3">Détails de l'abonnement</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Caractéristiques</th>
                                    <th>Détails</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Formule</strong></td>
                                    <td>{{ $quote->formule_abonnement }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Activités</strong> (avec participation des prestataires de BC)</td>
                                    <td>
                                        @if($quote->formule_abonnement == 'Starter')
                                            2 activités
                                        @elseif($quote->formule_abonnement == 'Basic')
                                            3 activités
                                        @elseif($quote->formule_abonnement == 'Premium')
                                            4 activités
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>RDV médicaux</strong> (présentiel/visio)</td>
                                    <td>
                                        @if($quote->formule_abonnement == 'Starter')
                                            1 RDV
                                        @elseif($quote->formule_abonnement == 'Basic')
                                            2 RDV
                                        @elseif($quote->formule_abonnement == 'Premium')
                                            3 RDV
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>RDV médicaux supplémentaires</strong> (aux frais des salariés)</td>
                                    <td>
                                        @if($quote->formule_abonnement == 'Starter' || $quote->formule_abonnement == 'Basic')
                                            75€/RDV
                                        @elseif($quote->formule_abonnement == 'Premium')
                                            50€/RDV
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Accès au chatbot</strong></td>
                                    <td>
                                        @if($quote->formule_abonnement == 'Starter')
                                            6 questions
                                        @elseif($quote->formule_abonnement == 'Basic')
                                            20 questions
                                        @elseif($quote->formule_abonnement == 'Premium')
                                            Illimité
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Accès aux fiches pratiques BC</strong></td>
                                    <td>Illimité</td>
                                </tr>
                                <tr>
                                    <td><strong>Conseils hebdomadaires</strong></td>
                                    <td>
                                        @if($quote->formule_abonnement == 'Starter')
                                            Non
                                        @elseif($quote->formule_abonnement == 'Basic')
                                            Oui (non personnalisés)
                                        @elseif($quote->formule_abonnement == 'Premium')
                                            Oui personnalisés (suggestion d'activités)
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Événements / Communautés</strong></td>
                                    <td>Accès illimité</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    @if(!empty($quote->services_details))
                        <div class="mt-4">
                            <h5 class="font-weight-bold">Description complémentaire</h5>
                            <div class="card bg-light">
                                <div class="card-body">
                                    {!! nl2br(e($quote->services_details)) !!}
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <!-- Espace réservé pour les informations supplémentaires -->
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="font-weight-bold">Récapitulatif financier</h6>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Nombre de salariés:</span>
                                        <span>{{ $quote->company_size }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Prix par salarié:</span>
                                        <span>
                                            @if($quote->formule_abonnement == 'Starter')
                                                180 €
                                            @elseif($quote->formule_abonnement == 'Basic')
                                                150 €
                                            @elseif($quote->formule_abonnement == 'Premium')
                                                100 €
                                            @endif
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Total HT:</span>
                                        <span>{{ number_format($quote->total_amount, 2, ',', ' ') }} €</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>TVA (20%):</span>
                                        <span>{{ number_format($quote->total_amount * 0.2, 2, ',', ' ') }} €</span>
                                    </div>
                                    <div class="d-flex justify-content-between font-weight-bold">
                                        <span>Total TTC:</span>
                                        <span>{{ number_format($quote->total_amount * 1.2, 2, ',', ' ') }} €</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            @if($quote->status == 'Pending')
                <div class="card shadow mb-4 border-left-warning">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-warning">Actions</h6>
                    </div>
                    <div class="card-body">
                        <p>Ce devis est en attente de votre décision. Vous pouvez l'accepter pour générer une facture, ou le rejeter.</p>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <form action="{{ route('quotes.reject', $quote->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir rejeter ce devis ?')">
                                    <i class="fas fa-times mr-1"></i> Rejeter
                                </button>
                            </form>
                            
                            <form action="{{ route('quotes.accept', $quote->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success" onclick="return confirm('Êtes-vous sûr de vouloir accepter ce devis et générer une facture ?')">
                                    <i class="fas fa-check mr-1"></i> Accepter
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @elseif($quote->status == 'Accepted')
                <div class="card shadow mb-4 border-left-success">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-success">Devis accepté</h6>
                    </div>
                    <div class="card-body">
                        <p>Ce devis a été accepté le {{ \Carbon\Carbon::parse($quote->updated_at)->format('d/m/Y') }}.</p>
                        
                        <!-- Vérifier si une facture est liée au devis -->
                        @if(isset($invoice))
                            <div class="alert alert-info mt-3">
                                <p class="mb-0">Une facture a été générée à partir de ce devis.</p>
                            </div>
                            
                            <a href="{{ route('invoices.show', $invoice->id) }}" class="btn btn-primary btn-block mt-3">
                                <i class="fas fa-file-invoice-dollar mr-1"></i> Voir la facture
                            </a>
                        @else
                            <div class="alert alert-warning mt-3">
                                <p class="mb-0">Aucune facture n'a encore été générée pour ce devis.</p>
                            </div>
                        @endif
                    </div>
                </div>
            @elseif($quote->status == 'Rejected')
                <div class="card shadow mb-4 border-left-danger">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-danger">Devis rejeté</h6>
                    </div>
                    <div class="card-body">
                        <p>Ce devis a été rejeté le {{ \Carbon\Carbon::parse($quote->updated_at)->format('d/m/Y') }}.</p>
                        
                        <a href="{{ route('quotes.create') }}" class="btn btn-primary btn-block mt-3">
                            <i class="fas fa-plus mr-1"></i> Créer un nouveau devis
                        </a>
                    </div>
                </div>
            @endif
            
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Validité du devis</h6>
                </div>
                <div class="card-body">
                    <p><strong>Date d'expiration:</strong> {{ \Carbon\Carbon::parse($quote->expiration_date)->format('d/m/Y') }}</p>
                    
                    @php
                        $expirationDate = \Carbon\Carbon::parse($quote->expiration_date);
                        $now = \Carbon\Carbon::now();
                    @endphp
                    
                    @if($expirationDate->isPast())
                        <div class="alert alert-danger mt-3">
                            <i class="fas fa-exclamation-triangle mr-1"></i> Ce devis a expiré.
                        </div>
                    @elseif($expirationDate->diffInDays($now) < 7)
                        <div class="alert alert-warning mt-3">
                            <i class="fas fa-exclamation-circle mr-1"></i> Ce devis expire dans {{ $expirationDate->diffInDays($now) }} jour(s).
                        </div>
                    @else
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-info-circle mr-1"></i> Ce devis est valable pendant encore {{ $expirationDate->diffInDays($now) }} jour(s).
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection