@extends('layouts.admin')

@section('content')
<div class="container">
    <h1>Importer des collaborateurs</h1>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Import CSV</h4>
            <a href="{{ route('employees.create') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Retour à l'ajout manuel
            </a>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <p>Vous pouvez importer plusieurs collaborateurs en une seule fois en utilisant un fichier CSV.</p>
                <ul>
                    <li>Le fichier doit contenir une ligne d'en-tête avec les noms des colonnes.</li>
                    <li>Les colonnes obligatoires sont : first_name, last_name, email, position.</li>
                    <li>Si aucun mot de passe n'est spécifié, un mot de passe aléatoire sera généré.</li>
                    <li>Le fichier ne doit <strong>pas contenir</strong> de lettre à accent (comme "é" par exemple).</li>
                </ul>
                <p>
                    <a href="{{ route('employees.download-template') }}" class="btn btn-sm btn-info">
                        <i class="bi bi-download"></i> Télécharger un modèle CSV
                    </a>
                </p>
            </div>

            @if(session('import_errors'))
            <div class="alert alert-warning">
                <h5>Erreurs d'importation :</h5>
                <ul>
                    @foreach(session('import_errors') as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('employees.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group mb-4">
                    <label for="csv_file">Fichier CSV</label>
                    <input type="file" name="csv_file" id="csv_file" class="form-control-file" accept=".csv" required>
                    <small class="form-text text-muted">Taille maximale: 2 Mo</small>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-upload"></i> Importer
                </button>
                <a href="{{ route('employees.index') }}" class="btn btn-secondary">Annuler</a>
            </form>
        </div>
    </div>
</div>
@endsection
