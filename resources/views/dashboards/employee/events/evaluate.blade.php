@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Évaluer l'événement</div>

                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('employee.events.evaluate.store', $event->id) }}">
                        @csrf
                        <div class="form-group">
                            <label>Note</label>
                            <select name="rating" class="form-control" required>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Commentaire</label>
                            <textarea name="comment" class="form-control"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">Envoyer</button>
                        <a href="{{ route('employee.events.history') }}" class="btn btn-secondary">Retour aux événements</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
