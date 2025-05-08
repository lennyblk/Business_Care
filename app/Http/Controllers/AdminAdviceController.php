<?php

namespace App\Http\Controllers;

use App\Http\Controllers\API\AdviceController as AdviceApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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

            return redirect()->route('admin.advice.index')->with('success', 'Conseil créé avec succès.');
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

            // Récupérer les catégories actives
            $categories = \App\Models\AdviceCategory::where('is_active', 1)->get();

            return view('dashboards.gestion_admin.advice.edit', compact('advice', 'categories'));
        } catch (\Exception $e) {
            Log::error('Exception lors de l\'édition d\'un conseil: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de l\'édition du conseil');
        }
    }

    public function update(Request $request, $id)
    {
        try {
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

            return redirect()->route('admin.advice.index')->with('success', 'Conseil supprimé avec succès.');
        } catch (\Exception $e) {
            Log::error('Exception lors de la suppression d\'un conseil: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de la suppression du conseil');
        }
    }
}
