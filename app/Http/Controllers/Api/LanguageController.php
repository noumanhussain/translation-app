<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Language\StoreLanguageRequest;
use App\Http\Requests\Language\UpdateLanguageRequest;
use App\Models\Language;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    /**
     * Display a listing of languages.
     */
    public function index(): JsonResponse
    {
        $languages = Language::select(['id', 'code', 'name', 'is_active'])
            ->get();

        return response()->json($languages);
    }

    /**
     * Store a newly created language.
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
     */
    public function show(Language $language): JsonResponse
    {
        return response()->json($language);
    }

    /**
     * Update the specified language.
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
     */
    public function destroy(Language $language): JsonResponse
    {
        $language->delete();
        return response()->json(null, 204);
    }
}
