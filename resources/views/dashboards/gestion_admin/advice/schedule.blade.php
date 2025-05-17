@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Programmer le conseil : {{ $advice->title }}</h2>

    <form action="{{ route('admin.advice.save-schedule', $advice->id) }}" method="POST">
        @csrf
        
        <div class="form-group mb-3">
            <label for="scheduled_date">Date de programmation</label>
            <input type="date" class="form-control @error('scheduled_date') is-invalid @enderror" 
                   id="scheduled_date" name="scheduled_date" 
                   value="{{ old('scheduled_date') }}" required>
            @error('scheduled_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group mb-3">
            <label for="target_audience">Public cible</label>
            <select class="form-control @error('target_audience') is-invalid @enderror" 
                    id="target_audience" name="target_audience" required>
                <option value="All">Tous les employés</option>
                <option value="Specific">Employés spécifiques</option>
            </select>
            @error('target_audience')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div id="criteriaSection" style="display: none;" class="mb-3">
            <div class="form-group">
                <label for="target_criteria">Critères de ciblage</label>
                <textarea class="form-control @error('target_criteria') is-invalid @enderror" 
                          id="target_criteria" name="target_criteria" rows="3"
                          placeholder='{"department": "IT", "position": "Developer"}'></textarea>
                <small class="form-text text-muted">Format JSON requis pour les critères spécifiques</small>
                @error('target_criteria')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Programmer</button>
        <a href="{{ route('admin.advice.index') }}" class="btn btn-secondary">Annuler</a>
    </form>
</div>

@push('scripts')
<script>
document.getElementById('target_audience').addEventListener('change', function() {
    const criteriaSection = document.getElementById('criteriaSection');
    criteriaSection.style.display = this.value === 'Specific' ? 'block' : 'none';
});
</script>
@endpush
@endsection
