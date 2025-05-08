<?php

namespace App\Http\Controllers;

use App\Http\Controllers\API\AdviceCategoryController as ApiAdviceCategoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminAdviceCategoryController extends Controller
{
    protected $apiController;

    public function __construct()
    {
        $this->apiController = new ApiAdviceCategoryController();
    }

    public function index()
    {
        try {
            $response = $this->apiController->index();
            $data = json_decode($response->getContent(), true);

            if ($response->getStatusCode() !== 200) {
                Log::error('Erreur lors de la récupération des catégories', ['response' => $data]);
                return back()->with('error', 'Une erreur est survenue lors de la récupération des catégories');
            }

            $categories = collect($data['data'])->map(function ($category) {
                return (object) $category;
            });

            return view('dashboards.gestion_admin.advice.categories.index', compact('categories'));
        } catch (\Exception $e) {
            Log::error('Exception lors de la récupération des catégories: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue');
        }
    }

    public function store(Request $request)
    {
        try {
            $response = $this->apiController->store($request);
            $data = json_decode($response->getContent(), true);

            if ($response->getStatusCode() !== 201) {
                return back()->withErrors(['error' => $data['message']])->withInput();
            }

            return redirect()->route('admin.advice-categories.index')
                ->with('success', 'Catégorie créée avec succès');
        } catch (\Exception $e) {
            Log::error('Exception lors de la création de la catégorie: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue')->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $response = $this->apiController->update($request, $id);
            $data = json_decode($response->getContent(), true);

            if ($response->getStatusCode() !== 200) {
                return back()->withErrors(['error' => $data['message']]);
            }

            return redirect()->route('admin.advice-categories.index')
                ->with('success', 'Catégorie mise à jour avec succès');
        } catch (\Exception $e) {
            Log::error('Exception lors de la mise à jour de la catégorie: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue');
        }
    }

    public function destroy($id)
    {
        try {
            $response = $this->apiController->destroy($id);

            if ($response->getStatusCode() !== 200) {
                $data = json_decode($response->getContent(), true);
                return back()->with('error', $data['message']);
            }

            return redirect()->route('admin.advice-categories.index')
                ->with('success', 'Catégorie supprimée avec succès');
        } catch (\Exception $e) {
            Log::error('Exception lors de la suppression de la catégorie: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue');
        }
    }
}
