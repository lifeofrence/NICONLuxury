<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->groupBy('group');
        return response()->json($settings);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'nullable',
            'settings.*.type' => 'sometimes|string',
            'settings.*.group' => 'sometimes|string',
        ]);

        foreach ($validated['settings'] as $setting) {
            Setting::set(
                $setting['key'],
                $setting['value'] ?? null,
                $setting['type'] ?? 'string',
                $setting['group'] ?? 'general'
            );
        }

        return response()->json(['message' => 'Settings updated successfully']);
    }
}
