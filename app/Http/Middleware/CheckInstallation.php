<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class CheckInstallation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $lockExists = File::exists(storage_path('app/installed.lock'));

        // Application counts as "installed" when the database is reachable
        // and there is at least one user record. This allows:
        // - fresh installs (no DB/users) to reach the installer
        // - imported databases with users (even without lock file) to go straight to login.
        $hasDatabaseAndUser = false;
        try {
            DB::connection()->getPdo();
            if (Schema::hasTable('users') && User::count() > 0) {
                $hasDatabaseAndUser = true;
            }
        } catch (\Throwable $e) {
            $hasDatabaseAndUser = false;
        }

        $installed = $hasDatabaseAndUser;

        $isInstallSpa = $request->is('install') || $request->is('install/*');
        $isInstallApi = $request->is('api/install') || $request->is('api/install/*');

        // Before installation, force everything to the installer except installer routes themselves
        if (! $installed && ! $isInstallSpa && ! $isInstallApi) {
            return redirect('/install');
        }

        // Once installed, let the SPA/router handle redirection to the correct login URL
        return $next($request);
    }
}

