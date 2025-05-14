<?php

namespace App\Http\Controllers;

use App\Http\Controllers\API\ProviderEvaluationController as APIController;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;

class ProviderEvaluationController extends Controller 
{
    protected $apiController;

    public function __construct()
    {
        $this->apiController = new APIController();
    }

    public function index()
    {
        try {
            $providerId = session('user_id');
            $response = $this->apiController->getProviderEvaluations($providerId);
            $data = json_decode($response->getContent(), true);

            if ($response->getStatusCode() !== 200) {
                return back()->with('error', $data['message'] ?? 'Une erreur est survenue');
            }

            // Convert API data to objects recursively
            $evaluationsCollection = collect($data['evaluations']['data'])->map(function ($item) {
                return $this->arrayToObject($item);
            });

            // Create paginator
            $evaluations = new LengthAwarePaginator(
                $evaluationsCollection,
                $data['evaluations']['total'],
                $data['evaluations']['per_page'],
                $data['evaluations']['current_page'],
                ['path' => request()->url()]
            );

            return view('dashboards.provider.evaluations.index', [
                'evaluations' => $evaluations,
                'averageRating' => $data['stats']['averageRating'],
                'totalEvaluations' => $data['stats']['totalEvaluations'],
                'evaluatedEvents' => $data['stats']['evaluatedEvents']
            ]);

        } catch (\Exception $e) {
            Log::error('Error in ProviderEvaluationController@index: ' . $e->getMessage());
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
                // Recursively convert nested arrays to objects
                $object->$key = $this->arrayToObject($value);
            } else {
                $object->$key = $value;
            }
        }

        return $object;
    }
}
