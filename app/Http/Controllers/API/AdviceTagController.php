<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AdviceTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdviceTagController extends Controller
{
    public function index()
    {
        try {
            $tags = AdviceTag::withCount('advices')
                ->select('advice_tag.*')
                ->selectRaw('(SELECT COUNT(*) FROM advice_has_tag WHERE advice_has_tag.tag_id = advice_tag.id) as advices_count')
                ->get();
            return response()->json(['data' => $tags], 200);
        } catch (\Exception $e) {
            Log::error('API: Erreur lors de la récupération des tags: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:50|unique:advice_tag,name'
            ]);

            $tag = AdviceTag::create($validated);
            return response()->json(['data' => $tag], 201);
        } catch (\Exception $e) {
            Log::error('API: Erreur lors de la création du tag: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $tag = AdviceTag::findOrFail($id);
            $validated = $request->validate([
                'name' => "required|string|max:50|unique:advice_tag,name,{$id}"
            ]);

            $tag->update($validated);
            return response()->json(['data' => $tag], 200);
        } catch (\Exception $e) {
            Log::error('API: Erreur lors de la mise à jour du tag: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $tag = AdviceTag::findOrFail($id);
            $tag->delete();
            return response()->json(['message' => 'Tag supprimé avec succès'], 200);
        } catch (\Exception $e) {
            Log::error('API: Erreur lors de la suppression du tag: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue'], 500);
        }
    }
}
