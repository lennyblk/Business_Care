<?php

namespace App\Http\Controllers;

use App\Http\Controllers\API\AdviceTagController as AdviceTagApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminAdviceTagController extends Controller
{
    protected $apiController;

    public function __construct()
    {
        $this->apiController = new AdviceTagApiController();
    }

    public function index()
    {
        try {
            $response = $this->apiController->index();
            $data = json_decode($response->getContent(), true);

            if ($response->getStatusCode() !== 200) {
                Log::error('Erreur lors de la récupération des tags', ['response' => $data]);
                return back()->with('error', 'Une erreur est survenue lors de la récupération des tags');
            }

            // Convertir le tableau en collection d'objets
            $tags = collect($data['data'])->map(function ($tag) {
                return (object) $tag;
            });

            return view('dashboards.gestion_admin.advice.tags.index', compact('tags'));
        } catch (\Exception $e) {
            Log::error('Exception lors de la récupération des tags: ' . $e->getMessage());
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

            return redirect()->route('admin.advice-tags.index')
                ->with('success', 'Tag créé avec succès');
        } catch (\Exception $e) {
            Log::error('Exception lors de la création du tag: ' . $e->getMessage());
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

            return redirect()->route('admin.advice-tags.index')
                ->with('success', 'Tag mis à jour avec succès');
        } catch (\Exception $e) {
            Log::error('Exception lors de la mise à jour du tag: ' . $e->getMessage());
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

            return redirect()->route('admin.advice-tags.index')
                ->with('success', 'Tag supprimé avec succès');
        } catch (\Exception $e) {
            Log::error('Exception lors de la suppression du tag: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue');
        }
    }
}
