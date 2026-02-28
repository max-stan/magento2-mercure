<?php

declare(strict_types=1);

namespace MaxStan\Mercure\Api;

use Symfony\Component\Mercure\HubInterface;

/**
 * Publish an update to the Mercure Hub.
 */
interface MercurePublisherInterface
{
    public function publish(array|string $topic, array $data): string;

    public function getMercureHub(): HubInterface;
}
