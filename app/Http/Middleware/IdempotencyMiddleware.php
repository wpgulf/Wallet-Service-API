<?php

namespace App\Http\Middleware;

use App\Models\IdempotencyRequest;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdempotencyMiddleware
{
  public function handle(Request $request, Closure $next): Response
  {
    $key = trim((string) $request->header('Idempotency-Key'));
    if ($key === '') {
      return response()->json(['error' => 'Missing Idempotency-Key header'], 400);
    }

    // Scope should differ by operation + resource
    // Example: route name + wallet id (if present)
    $routeName = $request->route()?->getName() ?? 'unknown';
    $walletId = $request->route('wallet') ?? $request->route('id') ?? '';
    $scope = $walletId !== '' ? "{$routeName}:wallet={$walletId}" : $routeName;

    $hash = hash('sha256', json_encode($request->all(), JSON_UNESCAPED_UNICODE));

    $existing = IdempotencyRequest::query()
      ->where('idem_key', $key)
      ->where('scope', $scope)
      ->first();

    if ($existing) {
      if ($existing->request_hash !== $hash) {
        return response()->json(['error' => 'Idempotency-Key reused with different payload'], 409);
      }
      return response()->json($existing->response_body, $existing->response_code);
    }

    /** @var Response $response */
    $response = $next($request);

    // Store only JSON responses safely
    $body = $response->getContent();
    $decoded = json_decode($body, true);
    if (json_last_error() === JSON_ERROR_NONE) {
      IdempotencyRequest::create([
        'idem_key' => $key,
        'scope' => $scope,
        'request_hash' => $hash,
        'response_code' => $response->getStatusCode(),
        'response_body' => $decoded,
        'created_at' => now(),
      ]);
    }

    return $response;
  }
}
