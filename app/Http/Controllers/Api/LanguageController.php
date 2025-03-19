<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LanguageController extends Controller
{
    public function index(): JsonResponse
    {
        $languages = Language::all();
        return response()->json($languages);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:languages,code',
            'name' => 'required|string|max:255',
            'is_active' => 'boolean'
        ]);

        $language = Language::create($validated);
        return response()->json($language, 201);
    }

    public function show(Language $language): JsonResponse
    {
        return response()->json($language);
    }

    public function update(Request $request, Language $language): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:10', Rule::unique('languages')->ignore($language->id)],
            'name' => 'required|string|max:255',
            'is_active' => 'boolean'
        ]);

        $language->update($validated);
        return response()->json($language);
    }

    public function destroy(Language $language): JsonResponse
    {
        $language->delete();
        return response()->json(null, 204);
    }
}
