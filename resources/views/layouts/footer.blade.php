<footer class="bg-dark text-white py-5 mt-auto">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4 mb-md-0">
                <h5>Business Care</h5>
                <p class="text-white">
                    Votre solution de gestion entreprise intelligente
                </p>
            </div>
            <div class="col-md-4 mb-4 mb-md-0">
                <h5>Liens rapides</h5>
                <ul class="list-unstyled">
                    <li><a href="{{ route('home') }}" class="text-decoration-none text-white">Accueil</a></li>
                    <li><a href="{{ route('services') }}" class="text-decoration-none text-white">Services</a></li>
                    <li><a href="{{ route('about') }}" class="text-decoration-none text-white">À propos</a></li>
                    <li><a href="{{ route('contact') }}" class="text-decoration-none text-white">Contact</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h5>Contact</h5>
                <address class="text-white">
                    <p>123 Rue de l'Innovation<br>75001 Paris, France</p>
                    <p>Email: contact@businesscare.com<br>
                    Tél: +33 1 23 45 67 89</p>
                </address>
            </div>
        </div>
        <hr class="my-4 bg-secondary">
        <div class="text-center text-white">
            <small>&copy; {{ date('Y') }} Business Care. Tous droits réservés.</small>
        </div>
    </div>
</footer>
