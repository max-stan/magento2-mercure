<?php

declare(strict_types=1);

namespace MaxStan\Mercure\Api;

/**
 * Attaches Mercure hub discovery Link header to the current REST response.
 */
interface MercureHttpManagementInterface
{
    public const string AUTHORIZATION_COOKIE_NAME = 'mercureAuthorization';

    /**
     * Set the `Link rel="mercure"` header on the current response.
     */
    public function attachLinkHeader(): void;

    /**
     * Attach mercureAuthorization cookie with a JWT authorizing the given topics.
     */
    public function attachAuthorizationCookie(): void;

    /**
     * Attach link header and mercureAuthorization cookie.
     */
    public function attach(): void;
}
