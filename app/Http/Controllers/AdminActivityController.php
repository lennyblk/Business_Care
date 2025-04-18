<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\API\EventController;
use App\Http\Controllers\API\CompanyController;
use Illuminate\Support\Facades\Log;
use stdClass;

class AdminActivityController extends Controller
{
    protected $apiEventController;
    protected $apiCompanyController;

    public function __construct()
    {
        $this->apiEventController = new EventController();
        $this->apiCompanyController = new CompanyController();
    }

    /**
     * Convertit un tableau associatif en objet stdClass récursivement
     */
    private function arrayToObject($array)
    {
        if (!is_array($array)) {
            return $array;
        }

        $object = new stdClass();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $object->$key = $this->arrayToObject($value);
            } else {
                $object->$key = $value;
            }
        }
        return $object;
    }

    /**
     * Convertit un tableau de tableaux associatifs en tableau d'objets
     */
    private function arrayToObjects($arrayOfArrays)
    {
        $objects = [];
        foreach ($arrayOfArrays as $array) {
            $objects[] = $this->arrayToObject($array);
        }
        return $objects;
    }

    public function index()
    {
        try {
            // Appel au contrôleur API pour récupérer les événements
            $response = $this->apiEventController->index();
            $data = json_decode($response->getContent(), true);

            if ($response->getStatusCode() !== 200) {
                Log::error('Erreur lors de la récupération des activités', [
                    'status' => $response->getStatusCode(),
                    'response' => $data
                ]);
                return back()->with('error', 'Erreur lors de la récupération des activités');
            }

            // Convertir le tableau associatif en tableau d'objets
            $events = $this->arrayToObjects($data['data'] ?? []);

            return view('dashboards.gestion_admin.activites.index', compact('events'));
        } catch (\Exception $e) {
            Log::error('Exception lors de la récupération des activités: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de la récupération des activités');
        }
    }

    public function create()
    {
        try {
            // Appel au contrôleur API pour récupérer les entreprises
            $response = $this->apiCompanyController->index();
            $data = json_decode($response->getContent(), true);

            if ($response->getStatusCode() !== 200) {
                Log::error('Erreur lors de la récupération des entreprises', [
                    'status' => $response->getStatusCode(),
                    'response' => $data
                ]);
                return back()->with('error', 'Erreur lors de la récupération des entreprises');
            }

            // Convertir le tableau associatif en tableau d'objets
            $companies = $this->arrayToObjects($data['data'] ?? []);

            return view('dashboards.gestion_admin.activites.create', compact('companies'));
        } catch (\Exception $e) {
            Log::error('Exception lors de la création d\'une activité: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de la création d\'une activité');
        }
    }

    public function store(Request $request)
    {
        try {
            // Validation côté web pour une meilleure expérience utilisateur
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'date' => 'required|date',
                'event_type' => 'required|in:Webinar,Conference,Sport Event,Workshop',
                'capacity' => 'required|integer',
                'location' => 'nullable|string|max:255',
                'company_id' => 'required|exists:company,id',
            ]);

            // Appel au contrôleur API pour créer l'événement
            $response = $this->apiEventController->store($request);
            $data = json_decode($response->getContent(), true);

            if ($response->getStatusCode() !== 201 && $response->getStatusCode() !== 200) {
                Log::error('Erreur lors de la création de l\'activité', [
                    'status' => $response->getStatusCode(),
                    'response' => $data,
                    'request' => $request->all()
                ]);

                $errors = $data['errors'] ?? ['Une erreur est survenue lors de la création de l\'activité'];
                return back()->withErrors($errors)->withInput();
            }

            return redirect()->route('admin.activities.index')->with('success', 'Activité créée avec succès.');
        } catch (\Exception $e) {
            Log::error('Exception lors de la création d\'une activité: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de la création de l\'activité')->withInput();
        }
    }

    public function show($id)
    {
        try {
            // Appel au contrôleur API pour récupérer l'événement
            $response = $this->apiEventController->show($id);
            $data = json_decode($response->getContent(), true);

            if ($response->getStatusCode() !== 200) {
                Log::error('Erreur lors de la récupération de l\'activité', [
                    'status' => $response->getStatusCode(),
                    'response' => $data,
                    'id' => $id
                ]);
                return back()->with('error', 'Activité non trouvée');
            }

            // Convertir le tableau associatif en objet
            $event = $this->arrayToObject($data['data'] ?? []);

            return view('dashboards.gestion_admin.activites.show', compact('event'));
        } catch (\Exception $e) {
            Log::error('Exception lors de l\'affichage d\'une activité: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de l\'affichage de l\'activité');
        }
    }

    public function edit($id)
    {
        try {
            // Appel au contrôleur API pour récupérer l'événement
            $eventResponse = $this->apiEventController->show($id);
            $eventData = json_decode($eventResponse->getContent(), true);

            if ($eventResponse->getStatusCode() !== 200) {
                Log::error('Erreur lors de la récupération de l\'activité pour modification', [
                    'status' => $eventResponse->getStatusCode(),
                    'response' => $eventData,
                    'id' => $id
                ]);
                return back()->with('error', 'Activité non trouvée');
            }

            // Convertir le tableau associatif en objet
            $event = $this->arrayToObject($eventData['data'] ?? []);

            // Appel au contrôleur API pour récupérer les entreprises
            $companiesResponse = $this->apiCompanyController->index();
            $companiesData = json_decode($companiesResponse->getContent(), true);

            if ($companiesResponse->getStatusCode() !== 200) {
                Log::error('Erreur lors de la récupération des entreprises', [
                    'status' => $companiesResponse->getStatusCode(),
                    'response' => $companiesData
                ]);
                return back()->with('error', 'Erreur lors de la récupération des entreprises');
            }

            // Convertir le tableau associatif en tableau d'objets
            $companies = $this->arrayToObjects($companiesData['data'] ?? []);

            return view('dashboards.gestion_admin.activites.edit', compact('event', 'companies'));
        } catch (\Exception $e) {
            Log::error('Exception lors de la modification d\'une activité: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de la modification de l\'activité');
        }
    }

    public function update(Request $request, $id)
    {
        try {
            // Validation côté web
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'date' => 'required|date',
                'event_type' => 'required|in:Webinar,Conference,Sport Event,Workshop',
                'capacity' => 'required|integer',
                'location' => 'nullable|string|max:255',
                'company_id' => 'required|exists:company,id',
            ]);

            // Appel au contrôleur API pour mettre à jour l'événement
            $response = $this->apiEventController->update($request, $id);
            $data = json_decode($response->getContent(), true);

            if ($response->getStatusCode() !== 200) {
                Log::error('Erreur lors de la mise à jour de l\'activité', [
                    'status' => $response->getStatusCode(),
                    'response' => $data,
                    'id' => $id,
                    'request' => $request->all()
                ]);

                $errors = $data['errors'] ?? ['Une erreur est survenue lors de la mise à jour de l\'activité'];
                return back()->withErrors($errors)->withInput();
            }

            return redirect()->route('admin.activities.index')->with('success', 'Activité mise à jour avec succès.');
        } catch (\Exception $e) {
            Log::error('Exception lors de la mise à jour d\'une activité: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de la mise à jour de l\'activité')->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            // Appel au contrôleur API pour supprimer l'événement
            $response = $this->apiEventController->destroy($id);
            $data = json_decode($response->getContent(), true);

            if ($response->getStatusCode() !== 200) {
                Log::error('Erreur lors de la suppression de l\'activité', [
                    'status' => $response->getStatusCode(),
                    'response' => $data,
                    'id' => $id
                ]);
                return back()->with('error', $data['message'] ?? 'Erreur lors de la suppression de l\'activité');
            }

            return redirect()->route('admin.activities.index')->with('success', 'Activité supprimée avec succès.');
        } catch (\Exception $e) {
            Log::error('Exception lors de la suppression d\'une activité: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de la suppression de l\'activité');
        }
    }
}
