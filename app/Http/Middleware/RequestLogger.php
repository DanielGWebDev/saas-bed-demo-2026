<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequestLogger
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('Access-Control-Allow-Private-Network', 'true');

        if (!str_contains($request->userAgent() ?? '', 'Symfony')) {
            $logPath = storage_path('logs/request_log.txt');
            file_put_contents(
                $logPath,
                $request->method() . ' ' . $request->path() . ' ' . $response->getStatusCode() . "\n",
                FILE_APPEND
            );
        }

        return $response;
    }
}
