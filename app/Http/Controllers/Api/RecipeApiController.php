<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RecipeApiController extends Controller
{
    public function index(Request $request)
    {
        $cacheKey = 'recipes_api_' . md5(json_encode($request->all()));

        $data = Cache::remember($cacheKey, 300, function () use ($request) {
            $query = Recipe::with('user:id,name');

            // Search by title
            if ($request->has('search')) {
                $query->where('title', 'like', '%' . $request->search . '%');
            }

            // Filter by cuisine
            if ($request->has('cuisine')) {
                $query->where('cuisine', $request->cuisine);
            }

            // Filter by difficulty
            if ($request->has('difficulty')) {
                $query->where('difficulty', $request->difficulty);
            }

            // Filter by max prep time
            if ($request->has('max_prep_time')) {
                $query->where('prep_time', '<=', (int) $request->max_prep_time);
            }

            // Sort
            $sortBy = $request->get('sort', 'created_at');
            $sortDir = $request->get('order', 'desc');
            $allowed = ['title', 'prep_time', 'servings', 'created_at', 'difficulty'];

            if (in_array($sortBy, $allowed)) {
                $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
            }

            // Limit
            $limit = min((int) $request->get('limit', 20), 100);

            return $query->limit($limit)->get();
        });

        return response()->json([
            'success' => true,
            'count' => count($data),
            'data' => $data,
        ]);
    }

    public function show(Recipe $recipe)
    {
        $recipe->load('user:id,name');

        return response()->json([
            'success' => true,
            'data' => $recipe,
        ]);
    }
}