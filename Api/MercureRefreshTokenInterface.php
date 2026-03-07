<?php

declare(strict_types=1);

namespace MaxStan\Mercure\Api;

/**
 * Refreshes the Mercure subscriber JWT token.
 *
 * Returns a fresh JWT that clients use to authenticate
 * Server-Sent Events subscriptions to the Mercure hub.
 *
 * @api
 */
interface MercureRefreshTokenInterface
{
    /**
     * Generate and return a fresh subscriber JWT.
     *
     * @return string JWT token string.
     */
    public function refresh(): string;
}
