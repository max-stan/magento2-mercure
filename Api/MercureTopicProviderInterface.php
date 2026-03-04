<?php

declare(strict_types=1);

namespace MaxStan\Mercure\Api;

/**
 * Provides Mercure topic URIs for a specific domain (e.g. LiveChat, notifications).
 *
 * Implementations are registered via di.xml as constructor arguments to
 * {@see MercurePublishTopicsProviderInterface} and {@see MercureSubscribeTopicsProviderInterface}.
 *
 * @api
 */
interface MercureTopicProviderInterface
{
    /**
     * Get private topics scoped to the given user.
     *
     * @param int $userId
     * @param int $userType One of UserContextInterface::USER_TYPE_* constants.
     * @return string[]
     */
    public function getPrivateTopics(int $userId, int $userType): array;

    /**
     * Get publicly accessible topics (no authentication required to subscribe).
     *
     * @return string[]
     */
    public function getPublicTopics(): array;
}
