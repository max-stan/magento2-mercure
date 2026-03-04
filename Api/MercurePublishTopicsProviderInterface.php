<?php

declare(strict_types=1);

namespace MaxStan\Mercure\Api;

/**
 * Aggregates Mercure publish topics from all registered topic providers.
 * Collected topics are used in publisher JWT claims to authorize publishing.
 *
 * @api
 */
interface MercurePublishTopicsProviderInterface
{
    /**
     * Get all private topics the given user is allowed to access.
     *
     * @param int $userId The Magento user ID (customer or admin).
     * @param int $userType One of UserContextInterface::USER_TYPE_* constants.
     * @return string[] List of private topic IRIs.
     */
    public function getPrivateTopics(int $userId, int $userType): array;

    /**
     * Get all publicly accessible topics.
     *
     * @return string[]
     */
    public function getPublicTopics(): array;

    /**
     * Get combined public and private topics for the given user.
     *
     * Returns only public topics when userId is null.
     *
     * @param int|null $userId
     * @param int|null $userType One of UserContextInterface::USER_TYPE_* constants.
     * @return string[]
     */
    public function getAllTopics(?int $userId, ?int $userType): array;
}
