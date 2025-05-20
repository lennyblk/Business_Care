<?php

namespace App\Http\Controllers;

use App\Http\Controllers\API\EmployeeAdviceController as ApiEmployeeAdviceController;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class EmployeeAdviceController extends Controller
{
    protected $apiController;

    public function __construct()
    {
        $this->apiController = new ApiEmployeeAdviceController();
    }

    public function index()
    {
        try {
            $response = $this->apiController->index(request());
            $data = json_decode($response->getContent(), true);

            if ($response->getStatusCode() !== 200) {
                Log::error('Erreur lors de la récupération des conseils', [
                    'status' => $response->getStatusCode(),
                    'response' => $data,
                ]);
                return back()->with('error', $data['message'] ?? 'Erreur lors de la récupération des conseils');
            }

            $advices = $this->arrayToObjects($data['data'] ?? []);
            return view('dashboards.employee.advice.index', compact('advices'));
        } catch (\Exception $e) {
            Log::error('Exception lors de la récupération des conseils: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue');
        }
    }

    public function show($id)
    {
        try {
            $advice = \App\Models\Advice::with('category', 'tags')->findOrFail($id);
            
            // Vérifier si l'employé a déjà donné un feedback
            $feedback = \App\Models\AdviceFeedback::where([
                'employee_id' => session('user_id'),
                'advice_id' => $id
            ])->first();

            return view('dashboards.employee.advice.show', compact('advice', 'feedback'));
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération du conseil: ' . $e->getMessage());
            return redirect()->route('employee.advice.index')->with('error', 'Conseil non trouvé.');
        }
    }

    public function storeFeedback(Request $request, $id)
    {
        try {
            $employeeId = session('user_id');
            
            $feedback = \App\Models\AdviceFeedback::where([
                'employee_id' => $employeeId,
                'advice_id' => $id
            ])->first();

            if ($feedback) {
                $feedback->update([
                    'rating' => $request->input('rating'),
                    'comment' => $request->input('comment'),
                    'is_helpful' => $request->input('is_helpful', false),
                ]);
                $message = 'Feedback mis à jour avec succès.';
            } else {
                \App\Models\AdviceFeedback::create([
                    'employee_id' => $employeeId,
                    'advice_id' => $id,
                    'rating' => $request->input('rating'),
                    'comment' => $request->input('comment'),
                    'is_helpful' => $request->input('is_helpful', false),
                ]);
                $message = 'Feedback soumis avec succès.';
            }

            return redirect()->route('employee.advice.show', $id)->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la soumission du feedback: ' . $e->getMessage());
            return redirect()->route('employee.advice.show', $id)->with('error', 'Une erreur est survenue lors de la soumission du feedback.');
        }
    }

    private function arrayToObjects($arrayOfArrays)
    {
        $objects = [];
        foreach ($arrayOfArrays as $array) {
            $objects[] = (object) $array;
        }
        return $objects;
    }
}
