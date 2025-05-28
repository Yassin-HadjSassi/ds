<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Cors
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->getMethod() === 'OPTIONS') {
            return response('', 200)
 ->header('Access-Control-Allow-Origin', '*')
 ->header('Access-Control-Allow-Methods', '*')
 ->header('Access-Control-Allow-Headers', '*')
 ->header('Access-Control-Allow-Credentials', 'false');
        }

 return $next($request)
    }
}