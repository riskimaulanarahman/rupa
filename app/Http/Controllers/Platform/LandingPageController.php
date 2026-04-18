<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\LandingContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class LandingPageController extends Controller
{
    public function index()
    {
        $contents = LandingContent::orderBy('section')->get();

        return view('platform.landing.index', compact('contents'));
    }

    public function edit(LandingContent $landingContent)
    {
        return view('platform.landing.edit', compact('landingContent'));
    }

    public function update(Request $request, LandingContent $landingContent)
    {
        $validated = $request->validate([
            'content' => 'required|array',
            'content.id' => 'required|string',
            'content.en' => 'required|string',
        ]);

        $landingContent->update([
            'content' => $validated['content'],
        ]);

        foreach (['id', 'en'] as $locale) {
            Cache::forget("landing_content.{$locale}.{$landingContent->key}");
        }

        return redirect()->route('platform.landing.index')->with('success', 'Konten landing page berhasil diperbarui.');
    }
}
