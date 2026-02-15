<?php

declare(strict_types=1);

namespace MaxStan\Mercure\Api;

/**
 * Returns a list of concrete Mercure topic IRIs that the given customer has permission to publish messages to
 */
interface TopicsResolver
{
    /**
     * Get authorized to publish messages IRIs for given customer
     */
    public function getTopics(?int $customerId): array;
}
