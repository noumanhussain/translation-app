<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Translation;
use App\Models\Language;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TranslationController extends Controller
{
    /**
     * Display a listing of translations with pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min($request->input('per_page', 50), 100); // Limit max items per page
        $query = Translation::query()->with(['language:id,code,name', 'tags:id,name']);

        // Filter by language
        if ($request->has('language')) {
            $query->whereHas('language', function ($q) use ($request) {
                $q->where('code', $request->language);
            });
        }

        // Filter by group
        if ($request->has('group')) {
            $query->where('group', $request->group);
        }

        // Filter by tag
        if ($request->has('tag')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('name', $request->tag);
            });
        }

        // Filter by key
        if ($request->has('key')) {
            $query->where('key', 'LIKE', '%' . $request->key . '%');
        }

        // Select only necessary fields
        $query->select(['id', 'key', 'value', 'language_id', 'group']);

        $translations = $query->paginate($perPage);

        return response()->json([
            'data' => $translations->items(),
            'meta' => [
                'current_page' => $translations->currentPage(),
                'last_page' => $translations->lastPage(),
                'per_page' => $translations->perPage(),
                'total' => $translations->total()
            ]
        ]);
    }

    /**
     * Store a newly created translation.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'key' => 'required|string',
            'value' => 'required|string',
            'language_id' => 'required|exists:languages,id',
            'group' => 'string|max:255',
            'tags' => 'array',
            'tags.*' => 'exists:tags,id'
        ]);

        $translation = DB::transaction(function () use ($validated) {
            $translation = Translation::create([
                'key' => $validated['key'],
                'value' => $validated['value'],
                'language_id' => $validated['language_id'],
                'group' => $validated['group'] ?? 'general'
            ]);

            if (isset($validated['tags'])) {
                $translation->tags()->attach($validated['tags']);
            }

            return $translation->load(['language:id,code,name', 'tags:id,name']);
        });

        return response()->json($translation, 201);
    }

    /**
     * Display the specified translation.
     */
    public function show(Translation $translation): JsonResponse
    {
        return response()->json(
            $translation->load(['language:id,code,name', 'tags:id,name'])
        );
    }

    /**
     * Update the specified translation.
     */
    public function update(Request $request, Translation $translation): JsonResponse
    {
        $validated = $request->validate([
            'key' => 'string',
            'value' => 'string',
            'language_id' => 'exists:languages,id',
            'group' => 'string|max:255',
            'tags' => 'array',
            'tags.*' => 'exists:tags,id'
        ]);

        DB::transaction(function () use ($translation, $validated) {
            $translation->update([
                'key' => $validated['key'] ?? $translation->key,
                'value' => $validated['value'] ?? $translation->value,
                'language_id' => $validated['language_id'] ?? $translation->language_id,
                'group' => $validated['group'] ?? $translation->group
            ]);

            if (isset($validated['tags'])) {
                $translation->tags()->sync($validated['tags']);
            }
        });

        return response()->json(
            $translation->load(['language:id,code,name', 'tags:id,name'])
        );
    }

    /**
     * Remove the specified translation.
     */
    public function destroy(Translation $translation): JsonResponse
    {
        $translation->delete();
        return response()->json(null, 204);
    }
}
