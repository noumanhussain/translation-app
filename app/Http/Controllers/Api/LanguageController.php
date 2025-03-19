<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Language\StoreLanguageRequest;
use App\Http\Requests\Language\UpdateLanguageRequest;
use App\Models\Language;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Languages",
 *     description="API Endpoints for Language Management"
 * )
 */
class LanguageController extends Controller
{
    /**
     * Display a listing of languages.
     *
     * @OA\Get(
     *     path="/api/languages",
     *     tags={"Languages"},
     *     summary="Get list of languages",
     *     description="Returns list of all languages",
     *     operationId="getLanguagesList",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="code", type="string", example="en"),
     *                 @OA\Property(property="name", type="string", example="English"),
     *                 @OA\Property(property="is_active", type="boolean", example=true)
     *             )
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function index(): JsonResponse
    {
        $languages = Language::select(['id', 'code', 'name', 'is_active'])
            ->get();

        return response()->json($languages);
    }

    /**
     * Store a newly created language.
     *
     * @OA\Post(
     *     path="/api/languages",
     *     tags={"Languages"},
     *     summary="Create a new language",
     *     description="Creates a new language and returns the created resource",
     *     operationId="storeLanguage",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"code", "name"},
     *             @OA\Property(property="code", type="string", maxLength=10, example="fr"),
     *             @OA\Property(property="name", type="string", maxLength=255, example="French"),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Language created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="code", type="string", example="fr"),
     *             @OA\Property(property="name", type="string", example="French"),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function store(StoreLanguageRequest $request): JsonResponse
    {
        $language = Language::create([
            'code' => $request->code,
            'name' => $request->name,
            'is_active' => $request->is_active ?? true,
        ]);

        return response()->json($language, 201);
    }

    /**
     * Display the specified language.
     *
     * @OA\Get(
     *     path="/api/languages/{id}",
     *     tags={"Languages"},
     *     summary="Get language by ID",
     *     description="Returns a single language",
     *     operationId="getLanguageById",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of language to return",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="code", type="string", example="en"),
     *             @OA\Property(property="name", type="string", example="English"),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Language not found"
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function show(Language $language): JsonResponse
    {
        return response()->json($language);
    }

    /**
     * Update the specified language.
     *
     * @OA\Put(
     *     path="/api/languages/{id}",
     *     tags={"Languages"},
     *     summary="Update an existing language",
     *     description="Updates a language and returns the updated resource",
     *     operationId="updateLanguage",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of language to update",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="string", maxLength=10, example="fr"),
     *             @OA\Property(property="name", type="string", maxLength=255, example="French"),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Language updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="code", type="string", example="fr"),
     *             @OA\Property(property="name", type="string", example="French"),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Language not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function update(UpdateLanguageRequest $request, Language $language): JsonResponse
    {
        $language->update([
            'code' => $request->code ?? $language->code,
            'name' => $request->name ?? $language->name,
            'is_active' => $request->is_active ?? $language->is_active,
        ]);

        return response()->json($language);
    }

    /**
     * Remove the specified language.
     *
     * @OA\Delete(
     *     path="/api/languages/{id}",
     *     tags={"Languages"},
     *     summary="Delete a language",
     *     description="Deletes a language",
     *     operationId="deleteLanguage",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of language to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Language deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Language not found"
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function destroy(Language $language): JsonResponse
    {
        $language->delete();
        return response()->json(null, 204);
    }
}
