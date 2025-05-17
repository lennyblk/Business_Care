<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\API\ServiceEvaluationController as ApiController;
use Illuminate\Support\Facades\Log;

class ServiceEvaluationController extends Controller
{
    protected $apiController;

    public function __construct()
    {
        $this->apiController = new ApiController();
    }

    public function create($id)
    {
        try {
            $response = $this->apiController->getEvaluationData($id);
            $data = json_decode($response->getContent(), true);

            if ($response->getStatusCode() !== 200) {
                throw new \Exception($data['message'] ?? 'Erreur lors du chargement');
            }

            return view('dashboards.employee.events.evaluate', [
                'event' => $data['event'],
                'employee' => $data['employee']
            ]);
        } catch (\Exception $e) {
            Log::error('Exception dans ServiceEvaluationController@create: ' . $e->getMessage());
            return redirect()->route('employee.events.history')
                           ->with('error', 'Une erreur est survenue lors du chargement');
        }
    }

    public function store(Request $request, $id)
    {
        try {
            $response = $this->apiController->store($request, $id);
            $data = json_decode($response->getContent(), true);

            if ($response->getStatusCode() !== 200) {
                return redirect()->back()->with('error', $data['message']);
            }

            return redirect()->route('employee.events.history')
                           ->with('success', 'Évaluation enregistrée avec succès');
        } catch (\Exception $e) {
            Log::error('Exception dans ServiceEvaluationController@store: ' . $e->getMessage());
            return redirect()->back()
                           ->with('error', 'Une erreur est survenue lors de l\'enregistrement');
        }
    }
}
