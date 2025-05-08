<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AdviceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdviceCategoryController extends Controller
{
    public function index()
    {
        try {
            $categories = AdviceCategory::all();
            return response()->json(['data' => $categories], 200);
        } catch (\Exception $e) {
            Log::error('API: Erreur lors de la récupération des catégories: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:100',
                'description' => 'nullable|string',
                'is_active' => 'boolean'
            ]);

            $category = AdviceCategory::create($validated);
            return response()->json(['data' => $category], 201);
        } catch (\Exception $e) {
            Log::error('API: Erreur lors de la création de la catégorie: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $category = AdviceCategory::findOrFail($id);
            $validated = $request->validate([
                'name' => 'required|string|max:100',
                'description' => 'nullable|string',
                'is_active' => 'boolean'
            ]);

            $category->update($validated);
            return response()->json(['data' => $category], 200);
        } catch (\Exception $e) {
            Log::error('API: Erreur lors de la mise à jour de la catégorie: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $category = AdviceCategory::findOrFail($id);
            $category->delete();
            return response()->json(['message' => 'Catégorie supprimée avec succès'], 200);
        } catch (\Exception $e) {
            Log::error('API: Erreur lors de la suppression de la catégorie: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue'], 500);
        }
    }
}
