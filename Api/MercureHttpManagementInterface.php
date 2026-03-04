<?php

declare(strict_types=1);

namespace MaxStan\Mercure\Api;

/**
 * Attaches Mercure hub discovery Link header and authorization cookie to the current REST response.
 *
 * Enables Mercure auto-discovery per the Mercure specification so that clients can
 * locate the hub URL and authenticate for Server-Sent Events subscriptions.
 *
 * @api
 */
interface MercureHttpManagementInterface
{
    public const string AUTHORIZATION_COOKIE_NAME = 'mercureAuthorization';

    /**
     * Set the `Link rel="mercure"` discovery header on the current response.
     *
     * @return void
     */
    public function attachLinkHeader(): void;

    /**
     * Attach mercureAuthorization cookie with a subscriber JWT.
     *
     * The cookie is set with SameSite=Strict and secure path.
     *
     * @return void
     */
    public function attachAuthorizationCookie(): void;

    /**
     * Attach both Link header and mercureAuthorization cookie.
     *
     * Convenience method combining {@see attachLinkHeader()} and {@see attachAuthorizationCookie()}.
     *
     * @return void
     */
    public function attach(): void;
}
