<?php

namespace App\Http\Controllers;

use App\Http\Controllers\API\ProviderController as APIProviderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminProviderController extends Controller
{
    protected $apiProvider;

    public function __construct(APIProviderController $apiProvider)
    {
        $this->apiProvider = $apiProvider;
    }

    private function convertResponse($response)
    {
        if ($response->getStatusCode() !== 200 && $response->getStatusCode() !== 201) {
            throw new \Exception('API request failed');
        }

        return json_decode($response->getContent(), true)['data'];
    }

    public function index()
    {
        try {
            $response = $this->apiProvider->index();
            $prestataires = $this->convertResponse($response);

            return view('dashboards.gestion_admin.prestataires.index', compact('prestataires'));
        } catch (\Exception $e) {
            Log::error('AdminProviderController@index error: '.$e->getMessage());
            return back()->with('error', 'Erreur lors de la récupération des prestataires');
        }
    }

    public function create()
    {
        return view('dashboards.gestion_admin.prestataires.create');
    }

    public function store(Request $request)
    {
        try {
            $response = $this->apiProvider->store($request);
            $this->convertResponse($response);

            return redirect()->route('admin.prestataires.index')
                ->with('success', 'Prestataire créé avec succès.');
        } catch (\Exception $e) {
            Log::error('AdminProviderController@store error: '.$e->getMessage());
            return back()->withInput()->with('error', 'Erreur lors de la création du prestataire');
        }
    }

    public function show($id)
    {
        try {
            $response = $this->apiProvider->show($id);
            $data = $this->convertResponse($response);

            return view('dashboards.gestion_admin.prestataires.show', [
                'prestataire' => $data,
                'disponibilites' => $data['availabilities'] ?? [],
                'evaluations' => $data['evaluations'] ?? [],
                'factures' => $data['invoices'] ?? []
            ]);
        } catch (\Exception $e) {
            Log::error('AdminProviderController@show error: '.$e->getMessage());
            return back()->with('error', 'Prestataire non trouvé');
        }
    }

    public function edit($id)
    {
        try {
            $response = $this->apiProvider->show($id);
            $prestataire = $this->convertResponse($response);

            return view('dashboards.gestion_admin.prestataires.edit', compact('prestataire'));
        } catch (\Exception $e) {
            Log::error('AdminProviderController@edit error: '.$e->getMessage());
            return back()->with('error', 'Prestataire non trouvé');
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $currentProviderResponse = $this->apiProvider->show($id);
            $currentProvider = json_decode($currentProviderResponse->getContent(), true)['data'];

            // Fusionner les données existantes avec les nouvelles données
            $mergedData = array_merge($currentProvider, $request->all());

            // Créer une nouvelle requête avec les données fusionnées
            $fullRequest = new Request($mergedData);

            // Appeler l'API pour mettre à jour
            $response = $this->apiProvider->update($fullRequest, $id);
            $responseData = json_decode($response->getContent(), true);

            // Vérifier s'il y a des erreurs dans la réponse
            if (isset($responseData['errors'])) {
                Log::error('AdminProviderController@update validation errors: ', $responseData['errors']);
                return back()->withErrors($responseData['errors'])->withInput();
            }

            return redirect()->route('admin.prestataires.index')
                ->with('success', 'Prestataire mis à jour avec succès.');
        } catch (\Exception $e) {
            Log::error('AdminProviderController@update error: '.$e->getMessage());
            return back()->withInput()->with('error', 'Erreur lors de la mise à jour du prestataire: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $response = $this->apiProvider->destroy($id);

            return redirect()->route('admin.prestataires.index')
                ->with('success', 'Prestataire supprimé avec succès.');
        } catch (\Exception $e) {
            Log::error('AdminProviderController@destroy error: '.$e->getMessage());
            return back()->with('error', 'Erreur lors de la suppression du prestataire');
        }
    }
}
