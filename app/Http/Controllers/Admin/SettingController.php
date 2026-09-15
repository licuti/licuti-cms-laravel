<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Requests\Admin\Setting\UpdateSettingRequest;
use App\Services\Admin\Setting\SettingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class SettingController extends Controller
{
    use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    public function __construct(
        private readonly SettingService $settingService
    ) {}

    public function edit(string $group = 'general'): View
    {
        $this->authorize('settings.view');

        $settings = $this->settingService->getGroup($group);
        $groups = ['general', 'seo', 'mail', 'social', 'appearance'];

        if (!in_array($group, $groups)) {
            abort(404);
        }

        return view('admin.settings.edit', compact('settings', 'group', 'groups'));
    }

    public function update(UpdateSettingRequest $request, string $group): RedirectResponse
    {
        $this->settingService->saveGroup($group, $request->except(['_token', '_method']));
        return redirect()->route('admin.settings.edit', $group)->with('success', 'Cập nhật cấu hình thành công!');
    }
}
