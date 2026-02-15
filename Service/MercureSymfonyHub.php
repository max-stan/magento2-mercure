<?php

declare(strict_types=1);

namespace MaxStan\Mercure\Service;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;
use Magento\Framework\Stdlib\CookieManagerInterface;
use MaxStan\Mercure\Api\MercureHubInterface;
use MaxStan\Mercure\Api\MercureTopicsAuthorizationInterface;
use MaxStan\Mercure\Model\Config;
use Symfony\Component\Mercure\Hub;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Jwt\FactoryTokenProvider;
use Symfony\Component\Mercure\Jwt\LcobucciFactory;
use Symfony\Component\Mercure\Jwt\TokenProviderInterface;

class MercureSymfonyHub implements MercureHubInterface
{
    private array $mercureHubs = [];
    private array $tokenProviders = [];

    public function __construct(
        private readonly Config $config,
        private readonly MercureTopicsAuthorizationInterface $mercureTopicsAuthorization,
        private readonly UserContextInterface $userContext,
        private readonly CookieManagerInterface $cookieManager,
        private readonly CookieMetadataFactory $cookieMetadataFactory
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getMercureHub(?int $customerId = null): HubInterface
    {
        if (isset($this->mercureHubs[$customerId])) {
            return $this->mercureHubs[$customerId];
        }

        $jwFactory = new LcobucciFactory($this->config->getPublisherJwtSecret());
        $provider = new FactoryTokenProvider(
            $jwFactory,
            publish: $this->mercureTopicsAuthorization->getAllowedTopics($customerId),
        );
        $this->mercureHubs[$customerId] = new Hub($this->config->getHubUrl(), $provider);

        return $this->mercureHubs[$customerId];
    }

    /**
     * @inheritDoc
     */
    public function getTokenProvider(?int $customerId = null): TokenProviderInterface
    {
        if (isset($this->tokenProviders[$customerId])) {
            return $this->tokenProviders[$customerId];
        }

        $jwFactory = new LcobucciFactory($this->config->getSubscriberJwtSecret());
        $this->tokenProviders[$customerId] = new FactoryTokenProvider(
            $jwFactory,
            subscribe: $this->mercureTopicsAuthorization->getAllowedTopics($customerId),
        );

        return $this->tokenProviders[$customerId];
    }

    /**
     * @inheritDoc
     */
    public function setAuthorizationHeader(): void
    {
        $customerId = (int)$this->userContext->getUserId();
        $jwt = $this->getTokenProvider($customerId)->getJwt();

        $metadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
            ->setPath('/')
            ->setSecure(true)
            ->setSameSite('Lax');

        $this->cookieManager->setPublicCookie('mercureAuthorization', $jwt, $metadata);
    }
}
