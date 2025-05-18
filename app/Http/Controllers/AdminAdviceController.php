<?php

namespace App\Http\Controllers;

use App\Http\Controllers\API\AdviceController as AdviceApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Advice;
use Validator;

class AdminAdviceController extends Controller
{
    protected $apiAdviceController;

    public function __construct()
    {
        $this->apiAdviceController = new AdviceApiController();
    }

    public function index()
    {
        try {
            $response = $this->apiAdviceController->index();
            $data = json_decode($response->getContent(), true);

            if ($response->getStatusCode() !== 200) {
                Log::error('Erreur lors de la récupération des conseils', [
                    'status' => $response->getStatusCode(),
                    'response' => $data
                ]);
                return back()->with('error', 'Erreur lors de la récupération des conseils');
            }

            $advices = $data['data'] ?? [];
            return view('dashboards.gestion_admin.advice.index', compact('advices'));
        } catch (\Exception $e) {
            Log::error('Exception lors de la récupération des conseils: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de la récupération des conseils');
        }
    }

    public function create()
    {
        try {
            $categories = \App\Models\AdviceCategory::where('is_active', 1)->get();
            $tags = \App\Models\AdviceTag::all();
            
            return view('dashboards.gestion_admin.advice.create', compact('categories', 'tags'));
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des données: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue.');
        }
    }

    public function store(Request $request)
    {
        try {
            $response = $this->apiAdviceController->store($request);
            $data = json_decode($response->getContent(), true);

            if ($response->getStatusCode() !== 201) {
                Log::error('Erreur lors de la création du conseil', [
                    'status' => $response->getStatusCode(),
                    'response' => $data
                ]);
                return back()->withErrors($data['errors'] ?? ['Une erreur est survenue'])->withInput();
            }

            // Créer automatiquement une programmation pour le conseil
            $advice = Advice::findOrFail($data['data']['id']);
            $schedule = new \App\Models\AdviceSchedule([
                'advice_id' => $advice->id,
                'scheduled_date' => $advice->publish_date,
                'target_audience' => 'All', // Par défaut tous les employés
                'target_criteria' => null
            ]);
            $schedule->save();

            return redirect()->route('admin.advice.index')->with('success', 'Conseil créé et programmé avec succès.');
        } catch (\Exception $e) {
            Log::error('Exception lors de la création d\'un conseil: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de la création du conseil')->withInput();
        }
    }

    public function edit($id)
    {
        try {
            $response = $this->apiAdviceController->show($id);
            $data = json_decode($response->getContent(), true);

            if ($response->getStatusCode() !== 200) {
                Log::error('Erreur lors de la récupération du conseil', [
                    'status' => $response->getStatusCode(),
                    'response' => $data
                ]);
                return back()->with('error', 'Conseil non trouvé');
            }

            $advice = $data['data'] ?? [];
            $categories = \App\Models\AdviceCategory::where('is_active', 1)->get();
            $tags = \App\Models\AdviceTag::all();

            return view('dashboards.gestion_admin.advice.edit', compact('advice', 'categories', 'tags'));
        } catch (\Exception $e) {
            Log::error('Exception lors de l\'édition d\'un conseil: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de l\'édition du conseil');
        }
    }

    public function update(Request $request, $id)
    {
        try {
            // Si publish_date n'est pas fourni, récupérer la valeur existante
            if (!$request->has('publish_date')) {
                $advice = Advice::findOrFail($id);
                $request->merge(['publish_date' => $advice->publish_date]);
            }

            $response = $this->apiAdviceController->update($request, $id);
            $data = json_decode($response->getContent(), true);

            if ($response->getStatusCode() !== 200) {
                Log::error('Erreur lors de la mise à jour du conseil', [
                    'status' => $response->getStatusCode(),
                    'response' => $data
                ]);
                return back()->withErrors($data['errors'] ?? ['Une erreur est survenue'])->withInput();
            }

            return redirect()->route('admin.advice.index')->with('success', 'Conseil mis à jour avec succès.');
        } catch (\Exception $e) {
            Log::error('Exception lors de la mise à jour d\'un conseil: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de la mise à jour du conseil')->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $response = $this->apiAdviceController->destroy($id);

            if ($response->getStatusCode() !== 200) {
                $data = json_decode($response->getContent(), true);
                Log::error('Erreur lors de la suppression du conseil', [
                    'status' => $response->getStatusCode(),
                    'response' => $data
                ]);
                return back()->with('error', $data['message'] ?? 'Erreur lors de la suppression du conseil');
            }

            // Modifier la valeur de is_disabled à 1 (tinyint) au lieu de true
            \App\Models\AdviceSchedule::where('advice_id', $id)
                ->update(['is_disabled' => 1]);

            return redirect()->route('admin.advice.index')->with('success', 'Conseil supprimé avec succès.');
        } catch (\Exception $e) {
            Log::error('Exception lors de la suppression d\'un conseil: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de la suppression du conseil');
        }
    }

    public function schedule($id)
    {
        try {
            $advice = Advice::findOrFail($id);
            return view('dashboards.gestion_admin.advice.schedule', compact('advice'));
        } catch (\Exception $e) {
            Log::error('Exception lors de la planification du conseil: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue');
        }
    }

    public function scheduledAdvices()
    {
        try {
            $response = $this->apiAdviceController->scheduled();
            $data = json_decode($response->getContent(), true);

            if ($response->getStatusCode() !== 200) {
                Log::error('Erreur lors de la récupération des conseils programmés', [
                    'status' => $response->getStatusCode(),
                    'response' => $data
                ]);
                return back()->with('error', 'Erreur lors de la récupération des conseils programmés');
            }

            $scheduledAdvices = $data['data'];
            return view('dashboards.gestion_admin.advice.scheduled', compact('scheduledAdvices'));
        } catch (\Exception $e) {
            Log::error('Exception lors de la récupération des conseils programmés: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue');
        }
    }

    public function saveSchedule(Request $request, $id)
    {
        try {
            $response = $this->apiAdviceController->saveSchedule($request, $id);
            $data = json_decode($response->getContent(), true);

            if ($response->getStatusCode() !== 201) {
                Log::error('Erreur lors de la programmation du conseil', [
                    'status' => $response->getStatusCode(),
                    'response' => $data
                ]);
                return back()->withErrors($data['errors'] ?? ['Une erreur est survenue'])->withInput();
            }

            return redirect()->route('admin.advice.index')
                ->with('success', 'Conseil programmé avec succès pour le ' . $request->scheduled_date);
        } catch (\Exception $e) {
            Log::error('Exception lors de la programmation du conseil: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue')->withInput();
        }
    }

    public function toggleSchedule($id)
    {
        try {
            $response = $this->apiAdviceController->toggleSchedule($id);
            $data = json_decode($response->getContent(), true);

            if ($response->getStatusCode() !== 200) {
                Log::error('Erreur lors du changement de statut', [
                    'status' => $response->getStatusCode(),
                    'response' => $data
                ]);
                return back()->with('error', $data['message'] ?? 'Une erreur est survenue');
            }

            $status = $data['data']['is_disabled'] ? 'désactivée' : 'activée';
            return back()->with('success', "La programmation a été $status avec succès.");
        } catch (\Exception $e) {
            Log::error('Exception lors du changement de statut: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue');
        }
    }
}
