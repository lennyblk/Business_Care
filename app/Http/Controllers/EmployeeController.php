<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class EmployeeController extends Controller
{
    protected $apiBaseUrl;

    public function __construct()
    {
        // Configurez l'URL de base de votre API - ajustez selon votre configuration
        $this->apiBaseUrl = config('app.url') . '/api';
    }

    public function index()
    {
        $userType = session('user_type');

        if ($userType === 'employe') {
            // Récupérer l'ID et l'email de l'employé connecté depuis la session
            $userId = (int)session('user_id');
            $userEmail = session('user_email');

            // Appel à l'API pour obtenir l'employé connecté
            $response = Http::get("{$this->apiBaseUrl}/employees/current", [
                'user_id' => $userId,
                'user_email' => $userEmail
            ]);

            if ($response->failed()) {
                return redirect()->route('login')->withErrors(['error' => 'Impossible de déterminer l\'employé connecté.']);
            }

            $apiData = $response->json();

            if (!$apiData['success']) {
                return redirect()->route('login')->withErrors(['error' => $apiData['message'] ?? 'Erreur lors de la récupération de l\'employé.']);
            }

            $employee = $apiData['data'];

            // Si on a trouvé l'employé, on met à jour la session
            if ($employee) {
                session(['user_id' => $employee['id']]);

                // Obtenir les événements pour cet employé
                $eventsResponse = Http::get("{$this->apiBaseUrl}/employees/{$employee['id']}/events");

                if ($eventsResponse->successful()) {
                    $eventsData = $eventsResponse->json()['data'];
                    $allEvents = $eventsData['allEvents'];
                    $myEvents = $eventsData['myEvents'];

                    return view('dashboards.employee.events.index', compact('allEvents', 'myEvents', 'employee'));
                }
            }
        }

        return redirect()->route('login')->withErrors(['error' => 'Impossible de déterminer l\'employé connecté.']);
    }

    public function register(Request $request, $id)
    {
        $userType = session('user_type');

        if ($userType === 'employe') {
            // On récupère l'ID de l'employé depuis la session
            $userId = (int)session('user_id');

            // Appel à l'API pour inscrire l'employé à l'événement
            $response = Http::post("{$this->apiBaseUrl}/employees/{$userId}/events/{$id}/register");

            if ($response->successful()) {
                return redirect()->route('employee.events.index')
                    ->with('success', 'Vous êtes maintenant inscrit à cet événement.');
            } else {
                $apiData = $response->json();
                $message = $apiData['message'] ?? 'Erreur lors de l\'inscription à l\'événement.';

                return redirect()->route('employee.events.index')
                    ->with('warning', $message);
            }
        }

        return redirect()->route('login')
            ->withErrors(['error' => 'Impossible de déterminer l\'employé connecté.']);
    }

    public function cancelRegistration($id)
    {
        $userType = session('user_type');

        if ($userType === 'employe') {
            // On récupère l'ID de l'employé depuis la session
            $userId = (int)session('user_id');

            // Appel à l'API pour annuler l'inscription
            $response = Http::delete("{$this->apiBaseUrl}/employees/{$userId}/events/{$id}/cancel");

            if ($response->successful()) {
                return redirect()->route('employee.events.index')
                    ->with('success', 'Votre inscription a été annulée.');
            } else {
                $apiData = $response->json();
                $message = $apiData['message'] ?? 'Erreur lors de l\'annulation de l\'inscription.';

                return redirect()->route('employee.events.index')
                    ->with('warning', $message);
            }
        }

        return redirect()->route('login')
            ->withErrors(['error' => 'Impossible de déterminer l\'employé connecté.']);
    }
}
