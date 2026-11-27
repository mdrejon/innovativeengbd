<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SystemTools;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Maintenance actions triggered from the dashboard "System tools" panel.
 */
class SystemController extends Controller
{
    public function __construct(protected SystemTools $tools) {}

    public function clearCache(): RedirectResponse
    {
        return $this->run('Cache cleared. Config, route and view caches were removed.', fn () => $this->tools->clearCache());
    }

    public function optimize(Request $request): RedirectResponse
    {
        $this->authorizeSuperAdmin($request);

        return $this->run('Site optimized: config, routes, events and views are cached. Run "Clear cache" after editing .env.', fn () => $this->tools->optimize());
    }

    public function migrate(Request $request): RedirectResponse
    {
        $this->authorizeSuperAdmin($request);

        return $this->run('Database migrations ran.', fn () => $this->tools->migrate());
    }

    public function seed(Request $request): RedirectResponse
    {
        $this->authorizeSuperAdmin($request);

        return $this->run('Default content inserted. Existing content was left untouched.', fn () => $this->tools->seedContent());
    }

    public function networkCheck(): RedirectResponse
    {
        return $this->run('Network status checks ran.', fn () => $this->tools->checkNetwork());
    }

    public function storageLink(Request $request): RedirectResponse
    {
        $this->authorizeSuperAdmin($request);

        return $this->run('Storage link created: uploads are now served from /storage.', fn () => $this->tools->linkStorage());
    }

    /**
     * @param  callable(): string  $action
     */
    protected function run(string $success, callable $action): RedirectResponse
    {
        try {
            $output = $action();
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()->route('admin.dashboard')
                ->with('error', 'The action failed: '.$exception->getMessage())
                ->with('system_output', $exception->getMessage());
        }

        return redirect()->route('admin.dashboard')
            ->with('success', $success)
            ->with('system_output', $output);
    }

    protected function authorizeSuperAdmin(Request $request): void
    {
        abort_unless($request->user()->isSuperAdmin(), 403, 'Only a super admin can run this action.');
    }
}
