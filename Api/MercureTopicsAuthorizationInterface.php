<?php

declare(strict_types=1);

namespace MaxStan\Mercure\Api;

/**
 * Interface for determining which Mercure topics a customer can publish to
 *
 * This service builds a list of authorized topics
 */
interface MercureTopicsAuthorizationInterface
{
    /**
     * Get allowed topics for given customer (only private)
     */
    public function getAllowedTopics(?int $customerId = null): array;
}
