<?php

namespace App\Http\Middleware;

use App\Models\AuthorPlan;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAuthorHasPlan
{
    /**
     * Bloque l'accès aux routes de publication si l'auteur n'a pas de forfait actif.
     * Routes concernées : author.books.create, author.books.store
     */
    public function handle(Request $request, Closure $next): Response
    {
        $activePlan = AuthorPlan::where('user_id', Auth::id())
            ->where('status', 'active')
            ->with('plan')
            ->latest()
            ->first();

        $hasActivePlan = $activePlan && $activePlan->isActive();

        if (!$hasActivePlan) {
            $message = 'Vous devez souscrire à un forfait pour publier un livre sur LireX.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 403);
            }

            return redirect()->route('author.plans.index')->with('error', $message);
        }

        return $next($request);
    }
}
