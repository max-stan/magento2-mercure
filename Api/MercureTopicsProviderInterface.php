<?php

declare(strict_types=1);

namespace MaxStan\Mercure\Api;

/**
 * Returns a list of public and private topics
 */
interface MercureTopicsProviderInterface
{
    /**
     * Get all public topics
     */
    public function getPublicTopics(): array;

    /**
     * Get all private topics customer is allowed to publish to
     */
    public function getPrivateTopics(int $customerId): array;
}
