<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\API\EventController as ApiEventController;
use App\Models\Employee;
use App\Models\Event;
use App\Models\ServiceEvaluation;
use Illuminate\Support\Facades\Log;

class EventController extends Controller
{
    protected $apiController;

    public function __construct()
    {
        $this->apiController = new ApiEventController();
    }

    private function arrayToObject($array)
    {
        if (!is_array($array)) {
            return $array;
        }

        $object = new \stdClass();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $object->$key = $this->arrayToObject($value);
            } else {
                $object->$key = $value;
            }
        }
        return $object;
    }

    private function arrayToObjects($arrayOfArrays)
    {
        return collect($arrayOfArrays)->map(function($array) {
            return $this->arrayToObject($array);
        });
    }

    public function index()
    {
        try {
            $employee = Employee::where('id', session('user_id'))->first();
            
            if (!$employee) {
                return redirect()->back()->with('error', 'Accès non autorisé');
            }

            $companyEventsResponse = $this->apiController->getByCompany($employee->company_id);
            $allEvents = $this->arrayToObjects(
                json_decode($companyEventsResponse->getContent(), true)['data'] ?? []
            );

            // Filtrer les événements actuels/futurs
            $currentEvents = $allEvents->filter(function($event) {
                return \Carbon\Carbon::parse($event->date) >= now();
            });

            $myEventsResponse = $this->apiController->getRegisteredEmployees($employee->id);
            $myEvents = $this->arrayToObjects(
                json_decode($myEventsResponse->getContent(), true)['data'] ?? []
            );

            // Filtrer les événements actuels/futurs pour myEvents
            $currentMyEvents = $myEvents->filter(function($event) {
                return \Carbon\Carbon::parse($event->date) >= now();
            });

            return view('dashboards.employee.events.index', [
                'allEvents' => $currentEvents,
                'myEvents' => $currentMyEvents,
                'employee' => $employee
            ]);
        } catch (\Exception $e) {
            Log::error('Exception dans EventController@index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Une erreur est survenue');
        }
    }

    public function history()
    {
        try {
            $employee = Employee::where('id', session('user_id'))->first();
            
            if (!$employee) {
                return redirect()->back()->with('error', 'Employé non trouvé');
            }

            $response = $this->apiController->getHistory($employee->id);
            $data = json_decode($response->getContent(), true);

            if (!$data['success']) {
                return redirect()->back()->with('error', $data['message']);
            }

            $events = $this->arrayToObjects($data['data']);

            return view('dashboards.employee.events.history', compact('events'));
        } catch (\Exception $e) {
            Log::error('Exception dans EventController@history: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Une erreur est survenue lors du chargement de l\'historique');
        }
    }

    public function register($id)
    {
        try {
            $employee = Employee::where('id', session('user_id'))->first();
            
            if (!$employee) {
                return redirect()->back()->with('error', 'Accès non autorisé');
            }

            $response = $this->apiController->store(new Request([
                'employee_id' => $employee->id,
                'event_id' => $id
            ]));

            if ($response->getStatusCode() === 201) {
                return redirect()->back()->with('success', 'Inscription réussie !');
            }

            return redirect()->back()->with('warning', 'Erreur lors de l\'inscription');
        } catch (\Exception $e) {
            Log::error('Exception dans EventController@register: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Une erreur est survenue');
        }
    }

    public function cancel($id)
    {
        try {
            $employee = Employee::where('id', session('user_id'))->first();
            
            if (!$employee) {
                return redirect()->back()->with('error', 'Accès non autorisé');
            }

            $response = $this->apiController->destroy(new Request([
                'employee_id' => $employee->id
            ]), $id);

            if ($response->getStatusCode() === 200) {
                return redirect()->back()->with('success', 'Désinscription réussie !');
            }

            return redirect()->back()->with('warning', 'Erreur lors de la désinscription');
        } catch (\Exception $e) {
            Log::error('Exception dans EventController@cancel: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Une erreur est survenue');
        }
    }
}
