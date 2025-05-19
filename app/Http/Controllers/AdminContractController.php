<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\API\AdminContractController as ApiAdminContractController;
use Illuminate\Support\Facades\Log;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class AdminContractController extends Controller
{
    protected $apiController;

    public function __construct()
    {
        $this->apiController = new ApiAdminContractController();
    }

    public function index()
    {
        try {
            $response = $this->apiController->getPendingContracts();
            $data = json_decode($response->getContent(), true);

            if ($response->getStatusCode() !== 200) {
                return back()->with('error', 'Erreur lors de la récupération des contrats');
            }

            $pendingContracts = $this->arrayToObjects($data['data'] ?? []);

            return view('dashboards.gestion_admin.contracts.index', compact('pendingContracts'));
        } catch (\Exception $e) {
            Log::error('Exception lors de la récupération des contrats: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue');
        }
    }

    public function show($id)
    {
        try {
            // Chargement direct du modèle avec sa relation 'company'
            $contract = \App\Models\Contract::with('company')->findOrFail($id);

            // Ici, on garde le nom de variable "contract" pour correspondre à ce que la vue attend
            return view('dashboards.gestion_admin.contracts.show', compact('contract'));
        } catch (\Exception $e) {
            Log::error('Exception lors de l\'affichage du contrat: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue');
        }
    }

    public function approve($id)
    {
        try {
            $response = $this->apiController->approveContract($id);
            $data = json_decode($response->getContent(), true);

            if ($response->getStatusCode() !== 200) {
                return back()->with('error', $data['message'] ?? 'Erreur lors de l\'approbation');
            }

            return redirect()->route('admin.contracts.index')
                           ->with('success', 'Contrat approuvé avec succès');
        } catch (\Exception $e) {
            Log::error('Exception lors de l\'approbation du contrat: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue');
        }
    }

    public function reject($id)
    {
        try {
            $response = $this->apiController->rejectContract($id);
            $data = json_decode($response->getContent(), true);

            if ($response->getStatusCode() !== 200) {
                return back()->with('error', $data['message'] ?? 'Erreur lors du rejet');
            }

            return redirect()->route('admin.contracts.index')
                           ->with('success', 'Contrat rejeté avec succès');
        } catch (\Exception $e) {
            Log::error('Exception lors du rejet du contrat: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue');
        }
    }

    public function approveTermination($id)
    {
        try {
            $response = $this->apiController->approveTermination($id);
            $data = json_decode($response->getContent(), true);

            if ($response->getStatusCode() !== 200) {
                return back()->with('error', $data['message'] ?? 'Erreur lors de l\'approbation de la résiliation');
            }

            return redirect()->route('admin.contracts.index')
                ->with('success', 'La résiliation a été approuvée avec succès');
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'approbation de la résiliation: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue');
        }
    }

    public function rejectTermination($id)
    {
        try {
            $response = $this->apiController->rejectTermination($id);
            $data = json_decode($response->getContent(), true);

            if ($response->getStatusCode() !== 200) {
                return back()->with('error', $data['message'] ?? 'Erreur lors du rejet de la résiliation');
            }

            return redirect()->route('admin.contracts.index')
                ->with('success', 'La demande de résiliation a été rejetée');
        } catch (\Exception $e) {
            Log::error('Erreur lors du rejet de la résiliation: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue');
        }
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
        $objects = [];
        foreach ($arrayOfArrays as $array) {
            $objects[] = $this->arrayToObject($array);
        }
        return $objects;
    }
}
