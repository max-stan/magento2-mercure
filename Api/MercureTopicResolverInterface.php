<?php

declare(strict_types=1);

namespace MaxStan\Mercure\Api;

/**
 * Aggregates Mercure topics from all registered topic providers.
 */
interface MercureTopicResolverInterface
{
    /**
     * Get all private topics the given user is allowed to access.
     *
     * @param int $userId
     * @param int $userType One of UserContextInterface::USER_TYPE_* constants.
     * @return string[]
     */
    public function getAllowedPrivateTopics(int $userId, int $userType): array;

    /**
     * Get all publicly accessible topics.
     *
     * @return string[]
     */
    public function getAllowedPublicTopics(): array;

    /**
     * Get combined public and private topics for the given user.
     *
     * Returns only public topics when userId is null.
     *
     * @param int|null $userId
     * @param int|null $userType One of UserContextInterface::USER_TYPE_* constants.
     * @return string[]
     */
    public function getAllAllowedTopics(?int $userId, ?int $userType): array;
}
