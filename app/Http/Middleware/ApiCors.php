<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiCors
{
    public function handle(Request $request, Closure $next): Response
    {
        $allowed = $this->allowedOrigins();
        $origin = $request->headers->get('Origin');

        if ($request->isMethod('OPTIONS')) {
            return $this->cors(response('', 204), $origin, $allowed);
        }

        $response = $next($request);

        return $this->cors($response, $origin, $allowed);
    }

    protected function cors(Response $response, ?string $origin, array $allowed): Response
    {
        if ($origin && ($allowed === ['*'] || in_array($origin, $allowed, true))) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Vary', 'Origin');
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Authorization, Content-Type, X-Requested-With');
            $response->headers->set('Access-Control-Max-Age', '86400');
        }

        return $response;
    }

    protected function allowedOrigins(): array
    {
        $raw = (string) setting('api_cors_origins', '');
        if ($raw === '' || $raw === '*') {
            return ['*'];
        }

        return array_filter(array_map('trim', explode(',', $raw)));
    }
}
