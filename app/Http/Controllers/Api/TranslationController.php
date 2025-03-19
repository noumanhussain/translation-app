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
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Translations",
 *     description="API Endpoints for Translation Management"
 * )
 */
class TranslationController extends Controller
{
    /**
     * Display a listing of translations with pagination.
     *
     * @OA\Get(
     *     path="/api/translations",
     *     tags={"Translations"},
     *     summary="Get list of translations",
     *     description="Returns list of all translations with optional filtering",
     *     operationId="getTranslationsList",
     *     @OA\Parameter(
     *         name="language_id",
     *         in="query",
     *         description="Filter by language ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="group",
     *         in="query",
     *         description="Filter by translation group",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="tag",
     *         in="query",
     *         description="Filter by translation tag",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="key",
     *         in="query",
     *         description="Filter by translation key",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="language_id", type="integer", example=1),
     *                 @OA\Property(property="key", type="string", example="welcome.message"),
     *                 @OA\Property(property="value", type="string", example="Welcome to our application"),
     *                 @OA\Property(property="group", type="string", example="messages"),
     *                 @OA\Property(
     *                     property="tags",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="frontend"),
     *                         @OA\Property(property="description", type="string", example="Frontend translations")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
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
     *
     * @OA\Post(
     *     path="/api/translations",
     *     tags={"Translations"},
     *     summary="Create a new translation",
     *     description="Creates a new translation and returns the created resource",
     *     operationId="storeTranslation",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"language_id", "key", "value", "group"},
     *             @OA\Property(property="language_id", type="integer", example=1),
     *             @OA\Property(property="key", type="string", example="welcome.message"),
     *             @OA\Property(property="value", type="string", example="Welcome to our application"),
     *             @OA\Property(property="group", type="string", example="messages"),
     *             @OA\Property(
     *                 property="tags",
     *                 type="array",
     *                 @OA\Items(type="integer", example=1),
     *                 description="Array of tag IDs"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Translation created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="language_id", type="integer", example=1),
     *             @OA\Property(property="key", type="string", example="welcome.message"),
     *             @OA\Property(property="value", type="string", example="Welcome to our application"),
     *             @OA\Property(property="group", type="string", example="messages"),
     *             @OA\Property(
     *                 property="tags",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="frontend")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
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
     *
     * @OA\Get(
     *     path="/api/translations/{id}",
     *     tags={"Translations"},
     *     summary="Get translation by ID",
     *     description="Returns a single translation with its tags",
     *     operationId="getTranslationById",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of translation to return",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="language_id", type="integer", example=1),
     *             @OA\Property(property="key", type="string", example="welcome.message"),
     *             @OA\Property(property="value", type="string", example="Welcome to our application"),
     *             @OA\Property(property="group", type="string", example="messages"),
     *             @OA\Property(
     *                 property="tags",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="frontend")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Translation not found"
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function show(Translation $translation): JsonResponse
    {
        return response()->json(
            $translation->load(['language:id,code,name', 'tags:id,name'])
        );
    }

    /**
     * Update the specified translation.
     *
     * @OA\Put(
     *     path="/api/translations/{id}",
     *     tags={"Translations"},
     *     summary="Update an existing translation",
     *     description="Updates a translation and returns the updated resource",
     *     operationId="updateTranslation",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of translation to update",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="language_id", type="integer", example=1),
     *             @OA\Property(property="key", type="string", example="welcome.message"),
     *             @OA\Property(property="value", type="string", example="Welcome to our application"),
     *             @OA\Property(property="group", type="string", example="messages"),
     *             @OA\Property(
     *                 property="tags",
     *                 type="array",
     *                 @OA\Items(type="integer", example=1),
     *                 description="Array of tag IDs"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Translation updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="language_id", type="integer", example=1),
     *             @OA\Property(property="key", type="string", example="welcome.message"),
     *             @OA\Property(property="value", type="string", example="Welcome to our application"),
     *             @OA\Property(property="group", type="string", example="messages"),
     *             @OA\Property(
     *                 property="tags",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="frontend")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Translation not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
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
     *
     * @OA\Delete(
     *     path="/api/translations/{id}",
     *     tags={"Translations"},
     *     summary="Delete a translation",
     *     description="Deletes a translation",
     *     operationId="deleteTranslation",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of translation to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Translation deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Translation not found"
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function destroy(Translation $translation): JsonResponse
    {
        $translation->delete();
        return response()->json(null, 204);
    }

    /**
     * Get translations by key across all languages.
     *
     * @OA\Get(
     *     path="/api/translations/by-key",
     *     tags={"Translations"},
     *     summary="Get translations by key",
     *     description="Returns translations for a specific key across all languages",
     *     operationId="getTranslationsByKey",
     *     @OA\Parameter(
     *         name="key",
     *         in="query",
     *         description="Translation key to search for",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="group",
     *         in="query",
     *         description="Translation group",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="language_id", type="integer", example=1),
     *                 @OA\Property(property="key", type="string", example="welcome.message"),
     *                 @OA\Property(property="value", type="string", example="Welcome to our application"),
     *                 @OA\Property(property="group", type="string", example="messages"),
     *                 @OA\Property(
     *                     property="language",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="code", type="string", example="en"),
     *                     @OA\Property(property="name", type="string", example="English")
     *                 )
     *             )
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function getByKey(GetByKeyRequest $request): JsonResponse
    {
        $translations = Translation::where('key', $request->key)
            ->when($request->group, function ($query) use ($request) {
                return $query->where('group', $request->group);
            })
            ->with('language')
            ->get();

        return response()->json($translations);
    }
}
