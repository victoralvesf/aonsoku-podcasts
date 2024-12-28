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
        $username = $request->query('username');
        $serverUrl = $request->query('serverUrl');

        if (!$username || !$serverUrl) {
            return response()->json([
                'message' => 'username and serverUrl params are required'
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
