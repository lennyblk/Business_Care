@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Conseils Programmés</h1>
        <a href="{{ route('admin.advice.index') }}" class="btn btn-secondary">Retour aux conseils</a>
    </div>

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Titre</th>
                    <th>Date programmée</th>
                    <th>Public cible</th>
                    <th>Statut envoi</th>
                    <th>Statut activation</th>
                    <th>Date d'envoi</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($scheduledAdvices as $schedule)
                <tr>
                    <td>{{ $schedule['advice']['title'] }}</td>
                    <td>{{ \Carbon\Carbon::parse($schedule['scheduled_date'])->format('d/m/Y') }}</td>
                    <td>{{ $schedule['target_audience'] }}</td>
                    <td>
                        @if($schedule['is_sent'])
                            <span class="badge bg-success">Envoyé</span>
                        @else
                            <span class="badge bg-warning">En attente</span>
                        @endif
                    </td>
                    <td>
                        @if($schedule['is_disabled'])
                            <span class="badge bg-danger">Désactivé</span>
                        @else
                            <span class="badge bg-success">Actif</span>
                        @endif
                    </td>
                    <td>{{ $schedule['sent_at'] ? \Carbon\Carbon::parse($schedule['sent_at'])->format('d/m/Y H:i') : '-' }}</td>
                    <td>
                        <form action="{{ route('admin.advice.schedule.toggle', $schedule['id']) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn {{ $schedule['is_disabled'] ? 'btn-success' : 'btn-danger' }} btn-sm"
                                    onclick="return confirm('Êtes-vous sûr de vouloir {{ $schedule['is_disabled'] ? 'activer' : 'désactiver' }} cette programmation ?')">
                                {{ $schedule['is_disabled'] ? 'Activer' : 'Désactiver' }}
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
