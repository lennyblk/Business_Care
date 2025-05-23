@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="card shadow-lg">
        <head>
            <meta charset="UTF-8">
        </head>
        <div class="card-header bg-primary text-white">
            <h1 class="mb-0">{{ $advice->title }}</h1>
        </div>
        <div class="card-body">
            <div class="mb-4">
                <span class="badge bg-info text-dark">{{ $advice->category->name ?? 'N/A' }}</span>
                <span class="badge bg-secondary">Publié le: {{ $advice->publish_date }}</span>
            </div>
            <div class="mb-4">
                <p class="fs-5">{{ $advice->content }}</p>
            </div>
            <div class="mb-4">
                <p>
                    @foreach($advice->tags as $tag)
                        <span class="badge bg-success">{{ $tag->name }}</span>
                    @endforeach
                </p>
            </div>
            <!-- Section Feedback -->
            <div class="card mt-4">
                <div class="card-header">
                    <h3>Feedback</h3>
                </div>
                <div class="card-body">
                    @if($feedback)
                        <div class="alert alert-info">
                            Vous avez déjà donné votre feedback pour ce conseil:
                            <br>
                            Note: {{ $feedback->rating }}/5
                            <br>
                            Commentaire: {{ $feedback->comment }}
                        </div>
                    @else
                        <form action="{{ route('employee.advice.feedback', $advice->id) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="rating" class="form-label">Note</label>
                                <select class="form-select" id="rating" name="rating" required>
                                    <option value="1">1 - Mauvais</option>
                                    <option value="2">2 - Passable</option>
                                    <option value="3">3 - Moyen</option>
                                    <option value="4">4 - Bon</option>
                                    <option value="5">5 - Excellent</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="comment" class="form-label">Commentaire</label>
                                <textarea class="form-control" id="comment" name="comment" rows="3"
                                    placeholder="Partagez votre expérience..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-success">Envoyer mon avis</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
<style>
.btn-link {
    color: #6c757d;
    padding: 0;
}
.btn-link:hover {
    color: #0056b3;
}
.collapse {
    transition: all 0.3s ease;
}
</style>
@endpush
