<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SettingsRequest;
use App\Models\Setting;
use App\Services\Images\ImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function __construct(private readonly ImageService $images) {}

    public function edit(): View
    {
        return view('admin.settings.edit', [
            'settings' => Setting::allCached(),
        ]);
    }

    public function update(SettingsRequest $request): RedirectResponse
    {
        foreach ($request->safe()->except(['logo', 'favicon']) as $key => $value) {
            Setting::set($key, $value ?? '');
        }

        if ($request->hasFile('logo')) {
            $this->images->delete(Setting::get('logo_path') ?: null);
            Setting::set('logo_path', $this->images->store($request->file('logo'), 'branding', maxWidth: 400));
        }

        if ($request->hasFile('favicon')) {
            $this->images->delete(Setting::get('favicon_path') ?: null);
            Setting::set('favicon_path', $this->images->store($request->file('favicon'), 'branding', maxWidth: 64));
        }

        return back()->with('success', 'Paramètres enregistrés.');
    }
}
