<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Translation\GetByKeyRequest;
use App\Http\Requests\Translation\StoreTranslationRequest;
use App\Http\Requests\Translation\UpdateTranslationRequest;
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

        if ($request->has('language')) {
            $query->whereHas('language', function ($q) use ($request) {
                $q->where('code', $request->language);
            });
        }

        if ($request->has('group')) {
            $query->where('group', $request->group);
        }

        if ($request->has('tag')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('name', $request->tag);
            });
        }

        if ($request->has('key')) {
            $query->where('key', 'LIKE', '%' . $request->key . '%');
        }

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
    public function store(StoreTranslationRequest $request): JsonResponse
    {
        $translation = DB::transaction(function () use ($request) {
            $translation = Translation::create([
                'key' => $request->key,
                'value' => $request->value,
                'language_id' => $request->language_id,
                'group' => $request->group ?? 'general'
            ]);

            if ($request->has('tags')) {
                $translation->tags()->attach($request->tags);
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
    public function update(UpdateTranslationRequest $request, Translation $translation): JsonResponse
    {
        DB::transaction(function () use ($translation, $request) {
            $translation->update([
                'key' => $request->key ?? $translation->key,
                'value' => $request->value ?? $translation->value,
                'language_id' => $request->language_id ?? $translation->language_id,
                'group' => $request->group ?? $translation->group
            ]);

            if ($request->has('tags')) {
                $translation->tags()->sync($request->tags);
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

    /**
     * Get translations by key across all languages.
     */
    public function getByKey(GetByKeyRequest $request): JsonResponse
    {
        $translations = Translation::where('key', $request->key)
            ->with(['language:id,code,name', 'tags:id,name'])
            ->get(['id', 'key', 'value', 'language_id', 'group']);

        return response()->json($translations);
    }
}
