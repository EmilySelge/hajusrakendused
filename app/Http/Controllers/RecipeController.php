<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class RecipeController extends Controller
{
    public function index(Request $request)
    {
        $query = Recipe::with('user:id,name');

        if ($request->search) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->cuisine) {
            $query->where('cuisine', $request->cuisine);
        }

        if ($request->difficulty) {
            $query->where('difficulty', $request->difficulty);
        }

        $sortBy = $request->get('sort', 'created_at');
        $sortDir = $request->get('order', 'desc');
        $allowed = ['title', 'prep_time', 'servings', 'created_at'];

        if (in_array($sortBy, $allowed)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        }

        return Inertia::render('recipes/Index', [
            'recipes' => $query->paginate(12)->through(fn($recipe) => [
                'id' => $recipe->id,
                'title' => $recipe->title,
                'image' => $recipe->image,
                'description' => $recipe->description,
                'prep_time' => $recipe->prep_time,
                'servings' => $recipe->servings,
                'cuisine' => $recipe->cuisine,
                'difficulty' => $recipe->difficulty,
                'user' => $recipe->user ? ['id' => $recipe->user->id, 'name' => $recipe->user->name] : null,
                'created_at' => $recipe->created_at->format('d.m.Y'),
            ]),
            'filters' => [
                'search' => $request->search ?? '',
                'cuisine' => $request->cuisine ?? '',
                'difficulty' => $request->difficulty ?? '',
                'sort' => $sortBy,
                'order' => $sortDir,
            ],
            'cuisines' => Recipe::distinct()->whereNotNull('cuisine')->pluck('cuisine'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'required|url',
            'description' => 'required|string',
            'prep_time' => 'required|integer|min:1',
            'servings' => 'required|integer|min:1',
            'cuisine' => 'nullable|string|max:100',
            'difficulty' => 'required|in:easy,medium,hard',
        ]);

        $request->user()->recipes()->create($validated);

        Cache::flush();

        return redirect()->back();
    }

    public function update(Request $request, Recipe $recipe)
    {
        if ($request->user()->id !== $recipe->user_id) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'required|url',
            'description' => 'required|string',
            'prep_time' => 'required|integer|min:1',
            'servings' => 'required|integer|min:1',
            'cuisine' => 'nullable|string|max:100',
            'difficulty' => 'required|in:easy,medium,hard',
        ]);

        $recipe->update($validated);
        Cache::flush();

        return redirect()->back();
    }

    public function destroy(Request $request, Recipe $recipe)
    {
        if ($request->user()->id !== $recipe->user_id) {
            abort(403);
        }

        $recipe->delete();
        Cache::flush();

        return redirect()->back();
    }
}