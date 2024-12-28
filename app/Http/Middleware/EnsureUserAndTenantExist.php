<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
use App\Models\Tenant;

class EnsureUserAndTenantExist
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $username = $request->header('APP-USERNAME');
        $serverUrl = $request->header('APP-SERVER-URL');

        if (!$username || !$serverUrl) {
            return response()->json([
                'message' => 'APP-USERNAME and APP-SERVER-URL headers are required'
            ], 400);
        }

        $tenant = Tenant::firstOrCreate(['server_url' => $serverUrl]);

        $user = User::firstOrCreate(
            ['username' => $username],
            ['tenant_id' => $tenant->id]
        );

        $request->merge(['user' => $user, 'tenant' => $tenant]);

        return $next($request);
    }
}
