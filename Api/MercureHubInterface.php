<?php

declare(strict_types=1);

namespace MaxStan\Mercure\Api;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Jwt\TokenProviderInterface;

/**
 * Provides Mercure Hub instances with user-specific authorization.
 *
 * This interface ensures users can only access topics they are authorized for
 * by generating appropriate JWT tokens based on user context.
 */
interface MercureHubInterface
{
    /**
     * Returns a hub with JWT token containing only topics the customer is authorized to publish.
     * If customer ID is null, returns topics for guests
     */
    public function getMercureHub(?int $customerId = null): HubInterface;

    /**
     * Returns the JWT token provider used to generate authorization tokens
     * for Mercure Hub subscribe operations.
     */
    public function getTokenProvider(): TokenProviderInterface;

    /**
     * Sets the Authorization header with a JWT token
     * containing the current user's authorized topics.
     *
     * @throws CookieSizeLimitReachedException
     * @throws FailureToSendException
     * @throws InputException
     */
    public function setAuthorizationHeader(): void;
}
