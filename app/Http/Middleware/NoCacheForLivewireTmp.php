<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class NoCacheForLivewireTmp
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (
            $request->is('storage/livewire-tmp/*') ||
            $request->is('livewire/upload-file')
        ) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
        }

        return $response;
    }
}
