<?php

declare(strict_types=1);

namespace MaxStan\Mercure\Service;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\App\Response\HttpInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use MaxStan\Mercure\Api\MercureHttpManagementInterface;
use MaxStan\Mercure\Model\Config;
use MaxStan\Mercure\Model\Jwt\TokenProvider;
use Psr\Log\LoggerInterface;

readonly class MercureHttpManagement implements MercureHttpManagementInterface
{
    public function __construct(
        private HttpInterface $response,
        private Config $config,
        private CookieManagerInterface $cookieManager,
        private CookieMetadataFactory $cookieMetadataFactory,
        private LoggerInterface $logger,
        private TokenProvider $tokenProvider,
        private UserContextInterface $userContext
    ) {
    }

    /**
     * @inheritdoc
     */
    public function attachLinkHeader(): void
    {
        $this->response->setHeader(
            'Link',
            '<' . $this->config->getHubUrl() . '>; rel="mercure"'
        );
    }

    /**
     * @inheritdoc
     */
    public function attachAuthorizationCookie(): void
    {
        $jwt = $this->tokenProvider->getJwt();

        $metadata = $this->cookieMetadataFactory->createSensitiveCookieMetadata()
            ->setPath($this->config->getHubUrl())
            ->setSameSite('Strict');

        try {
            $this->cookieManager->setSensitiveCookie(self::AUTHORIZATION_COOKIE_NAME, $jwt, $metadata);
        } catch (LocalizedException $e) {
            $this->logger->error(
                '[MaxStan_Mercure] Something went during mercure authorization cookie set',
                ['e' => $e->getMessage(), 'user_id' => $this->userContext->getUserId()]
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function attach(): void
    {
        $this->attachLinkHeader();
        $this->attachAuthorizationCookie();
    }
}
