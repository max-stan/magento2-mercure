<?php

declare(strict_types=1);

namespace MaxStan\Mercure\Api;

use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Mercure\HubInterface;

/**
 * Publish an update to the Mercure Hub.
 */
interface MercurePublisherInterface
{
    /**
     * Publish an update to one or more Mercure topics.
     *
     * @param array|string $topic Topic URI(s) to publish to.
     * @param array $data Payload data (will be JSON-encoded).
     * @param string|null $event Optional event name to wrap the data with.
     * @return string The Mercure hub response ID.
     * @throws LocalizedException
     */
    public function publish(array|string $topic, array $data, ?string $event = null): string;

    /**
     * Get the configured Mercure hub instance.
     *
     * @return HubInterface
     */
    public function getMercureHub(): HubInterface;
}
