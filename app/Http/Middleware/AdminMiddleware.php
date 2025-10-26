<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
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
        $user = Auth::user();

        // el instanceof asegura al analizador que $user es App\Models\User
        if (! $user instanceof User || ! $user->isAdmin()) {
            return redirect('/')->with('error', 'Acceso denegado');
        }

        return $next($request);
    }
}
