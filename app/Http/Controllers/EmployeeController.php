<?php

namespace App\Http\Controllers;

use App\Http\Controllers\API\EmployeeController as ApiEmployeeController;
use App\Http\Controllers\API\EventController as ApiEventController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmployeeController extends Controller
{
    protected $apiEmployeeController;
    protected $apiEventController;

    public function __construct()
    {
        $this->apiEmployeeController = new ApiEmployeeController();
        $this->apiEventController = new ApiEventController();
    }

    /**
     * Convert an associative array to an object recursively.
     */
    private function arrayToObject($array)
    {
        if (!is_array($array)) {
            return $array;
        }

        $object = new \stdClass();
        foreach ($array as $key => $value) {
            $object->$key = is_array($value) ? $this->arrayToObject($value) : $value;
        }
        return $object;
    }

    public function index()
    {
        $userType = session('user_type');

        if ($userType === 'employe') {
            $userId = (int)session('user_id');
            $userEmail = session('user_email');

            try {
                $employeeResponse = $this->apiEmployeeController->show($userId);
                $employeeData = json_decode($employeeResponse->getContent(), true);

                if ($employeeResponse->getStatusCode() !== 200 || !$employeeData['success']) {
                    return redirect()->route('login')->withErrors(['error' => $employeeData['message'] ?? 'Erreur lors de la récupération de l\'employé.']);
                }

                $employee = $this->arrayToObject($employeeData['data']);

                $allEventsResponse = $this->apiEventController->index();
                $allEventsData = json_decode($allEventsResponse->getContent(), true);

                if ($allEventsResponse->getStatusCode() !== 200) {
                    return redirect()->route('login')->withErrors(['error' => 'Erreur lors de la récupération des événements.']);
                }

                // Convertir les événements en objets
                $allEvents = collect(array_map([$this, 'arrayToObject'], $allEventsData['data']));
                Log::info('Structure de allEvents:', ['allEvents' => $allEvents]);

                $myEventsResponse = $this->apiEventController->getRegisteredEmployees($userId);
                $myEventsData = json_decode($myEventsResponse->getContent(), true);

                if ($myEventsResponse->getStatusCode() !== 200) {
                    return redirect()->route('login')->withErrors(['error' => 'Erreur lors de la récupération des événements inscrits.']);
                }

                $myEvents = collect(array_map([$this, 'arrayToObject'], $myEventsData['data']));
                Log::info('Structure de myEvents:', ['myEvents' => $myEvents]);

                return view('dashboards.employee.events.index', compact('allEvents', 'myEvents', 'employee'));
            } catch (\Exception $e) {
                Log::error('Erreur dans EmployeeController@index : ' . $e->getMessage());
                return redirect()->route('login')->withErrors(['error' => 'Une erreur est survenue.']);
            }
        }

        return redirect()->route('login')->withErrors(['error' => 'Impossible de déterminer l\'employé connecté.']);
    }

    public function register(Request $request, $id)
    {
        $userType = session('user_type');

        if ($userType === 'employe') {
            $userId = (int)session('user_id');

            try {
                $response = $this->apiEventController->store($request->merge(['employee_id' => $userId, 'event_id' => $id]));

                if ($response->getStatusCode() === 201) {
                    return redirect()->route('employee.events.index')
                        ->with('success', 'Vous êtes maintenant inscrit à cet événement.');
                } else {
                    $apiData = json_decode($response->getContent(), true);
                    $message = $apiData['message'] ?? 'Erreur lors de l\'inscription à l\'événement.';

                    Log::error('Erreur lors de l\'inscription à l\'événement', [
                        'status_code' => $response->getStatusCode(),
                        'response' => $apiData
                    ]);

                    return redirect()->route('employee.events.index')
                        ->with('warning', $message);
                }
            } catch (\Exception $e) {
                Log::error('Erreur dans EmployeeController@register : ' . $e->getMessage());
                return redirect()->route('employee.events.index')
                    ->with('warning', 'Une erreur est survenue lors de l\'inscription.');
            }
        }

        return redirect()->route('login')
            ->withErrors(['error' => 'Impossible de déterminer l\'employé connecté.']);
    }

    public function cancelRegistration($id)
    {
        $userType = session('user_type');

        if ($userType === 'employe') {
            $userId = (int)session('user_id');

            try {
                $response = $this->apiEventController->destroy(new Request(['employee_id' => $userId]), $id);

                if ($response->getStatusCode() === 200) {
                    return redirect()->route('employee.events.index')
                        ->with('success', 'Votre inscription a été annulée.');
                } else {
                    $apiData = json_decode($response->getContent(), true);
                    $message = $apiData['message'] ?? 'Erreur lors de l\'annulation de l\'inscription.';

                    return redirect()->route('employee.events.index')
                        ->with('warning', $message);
                }
            } catch (\Exception $e) {
                Log::error('Erreur dans EmployeeController@cancelRegistration : ' . $e->getMessage());
                return redirect()->route('employee.events.index')
                    ->with('warning', 'Une erreur est survenue lors de l\'annulation.');
            }
        }

        return redirect()->route('login')
            ->withErrors(['error' => 'Impossible de déterminer l\'employé connecté.']);
    }
}