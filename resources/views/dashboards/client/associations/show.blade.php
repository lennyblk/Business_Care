@extends('layouts.app', ['skip_invoice' => true])

@section('title', $association['name'])

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
                    <a href="{{ route('client.associations.index') }}" class="list-group-item list-group-item-action active">Associations</a>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">{{ $association['name'] }}</h4>
                    <a href="{{ route('client.associations.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-8">
                            <h5>À propos</h5>
                            <p>{{ $association['description'] }}</p>

                            <div class="mt-3">
                                <h5>Domaine</h5>
                                <p>{{ $association['domain'] }}</p>
                            </div>

                            <div class="mt-3">
                                <h5>Coordonnées</h5>
                                <ul class="list-unstyled">
                                    @if(isset($association['contact_info']) && $association['contact_info'])
                                        <li><i class="fas fa-info-circle"></i> {{ $association['contact_info'] }}</li>
                                    @endif
                                    @if(isset($association['website']) && $association['website'])
                                        <li><i class="fas fa-globe"></i> <a href="{{ $association['website'] }}" target="_blank">{{ $association['website'] }}</a></li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <div class="card border-success">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">Faire un don</h5>
                            </div>
                            <div class="card-body">
                                <p>Vous pouvez soutenir cette association en faisant un don financier.</p>

                                <form action="{{ route('client.associations.donate', $association['id']) }}" method="POST" class="mt-3">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="amount" class="form-label">Montant du don (€)</label>
                                        <div class="input-group">
                                            <input type="number" id="amount" name="amount" class="form-control" min="1" step="1" required>
                                            <span class="input-group-text">€</span>
                                        </div>
                                        @error('amount')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-donate"></i> Faire un don
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h5>Comment nous aider</h5>
                        <div class="card-group">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Dons financiers</h5>
                                    <p class="card-text">Soutenez l'association grâce à vos dons financiers.</p>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Dons matériels</h5>
                                    <p class="card-text">Donnez du matériel informatique, des fournitures, ou d'autres ressources.</p>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Bénévolat</h5>
                                    <p class="card-text">Participez bénévolement aux actions de l'association.</p>
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
<script src="https://js.stripe.com/v3/"></script>
@endsection
