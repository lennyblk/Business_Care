@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Détails de la demande d'inscription</h1>

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Demande #{{ $registration->id }} -
                @if($registration->user_type == 'societe')
                    {{ $registration->company_name }}
                @else
                    {{ $registration->first_name }} {{ $registration->last_name }}
                @endif
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>Informations générales</h5>
                    <table class="table">
                        <tr>
                            <th>Type d'utilisateur</th>
                            <td>
                                @if($registration->user_type == 'societe')
                                    Société
                                @elseif($registration->user_type == 'employe')
                                    Employé
                                @elseif($registration->user_type == 'prestataire')
                                    Prestataire
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>{{ $registration->email }}</td>
                        </tr>
                        <tr>
                            <th>Date de demande</th>
                            <td>{{ $registration->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Statut</th>
                            <td>
                                @if($registration->status == 'pending')
                                    <span class="badge badge-warning">En attente</span>
                                @elseif($registration->status == 'approved')
                                    <span class="badge badge-success">Approuvée</span>
                                @elseif($registration->status == 'rejected')
                                    <span class="badge badge-danger">Rejetée</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h5>Informations spécifiques</h5>
                    <table class="table">
                        @if($registration->user_type == 'societe')
                            <tr>
                                <th>Nom de l'entreprise</th>
                                <td>{{ $registration->company_name }}</td>
                            </tr>
                            <tr>
                                <th>Adresse</th>
                                <td>{{ $registration->address }}</td>
                            </tr>
                            <tr>
                                <th>Code postal</th>
                                <td>{{ $registration->code_postal }}</td>
                            </tr>
                            <tr>
                                <th>Ville</th>
                                <td>{{ $registration->ville }}</td>
                            </tr>
                            <tr>
                                <th>Téléphone</th>
                                <td>{{ $registration->telephone }}</td>
                            </tr>
                            <tr>
                                <th>SIRET</th>
                                <td>{{ $registration->siret }}</td>
                            </tr>
                        @elseif($registration->user_type == 'employe')
                            <tr>
                                <th>Prénom</th>
                                <td>{{ $registration->first_name }}</td>
                            </tr>
                            <tr>
                                <th>Nom</th>
                                <td>{{ $registration->last_name }}</td>
                            </tr>
                            <tr>
                                <th>Entreprise</th>
                                <td>{{ $registration->company_name }}</td>
                            </tr>
                            <tr>
                                <th>Poste</th>
                                <td>{{ $registration->position }}</td>
                            </tr>
                            <tr>
                                <th>Département</th>
                                <td>{{ $registration->departement }}</td>
                            </tr>
                            <tr>
                                <th>Téléphone</th>
                                <td>{{ $registration->telephone }}</td>
                            </tr>
                            @elseif($registration->user_type == 'prestataire')
                            <tr>
                                <th>Prénom</th>
                                <td>{{ $registration->first_name }}</td>
                            </tr>
                            <tr>
                                <th>Nom</th>
                                <td>{{ $registration->last_name }}</td>
                            </tr>
                            <tr>
                                <th>Domaines</th>
                                <td>{{ $registration->domains }}</td>
                            </tr>
                            <tr>
                                <th>Téléphone</th>
                                <td>{{ $registration->telephone }}</td>
                            </tr>
                            <tr>
                                <th>Description</th>
                                <td>{{ $registration->description }}</td>
                            </tr>
                            <tr>
                                <th>Type d'activité</th>
                                <td>
                                    @php
                                        // Débogage
                                        if(isset($registration->activity_type)) {
                                            echo "Type d'activité : " . $registration->activity_type;
                                        } else {
                                            echo "Aucun type d'activité défini";
                                        }
                                    @endphp
                                </td>
                            </tr>
                            <tr>
                                <th>Tarif horaire</th>
                                <td>{{ $registration->tarif_horaire }} €</td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>

            <div class="mt-4 text-center">
            <form action="{{ route('admin.inscriptions.approve', $registration->id) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-check"></i> Approuver
                </button>
            </form>
            <form action="{{ route('admin.inscriptions.reject', $registration->id) }}" method="POST" class="d-inline ml-2">
                @csrf
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-times"></i> Rejeter
                </button>
            </form>
            <a href="{{ route('admin.inscriptions.index') }}" class="btn btn-secondary ml-2">
                <i class="fas fa-arrow-left"></i> Retour à la liste
            </a>
            </div>
        </div>
    </div>
</div>
@endsection
