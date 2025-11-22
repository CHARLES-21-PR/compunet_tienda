<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Temporal debug logging to diagnose unexpected logout when filtering dashboard
        try {
            Log::info('AdminMiddleware incoming', [
                'path' => $request->path(),
                'method' => $request->method(),
                'query' => $request->query(),
                'auth_id' => Auth::id(),
                'session_id' => session()->getId(),
                'cookies' => $request->cookies->all(),
                'ip' => $request->ip(),
                'user_agent' => $request->header('User-Agent')
            ]);
        } catch (\Throwable $_e) {
            // do not block request for logging failures
        }

        $user = Auth::user();

        // el instanceof asegura al analizador que $user es App\Models\User
        if (! $user instanceof User || ! $user->isAdmin()) {
            return redirect('/')->with('error', 'Acceso denegado');
        }

        return $next($request);
    }
}
