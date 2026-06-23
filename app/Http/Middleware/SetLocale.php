<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocale
{
    protected array $supported = ['fr','en','es','zh','pt','ar','ln','sw','de','ha','kt','ru'];

    public function handle(Request $request, Closure $next)
    {
        $locale = session('locale', config('app.locale', 'fr'));
        if (in_array($locale, $this->supported)) {
            App::setLocale($locale);
        }
        return $next($request);
    }
}
