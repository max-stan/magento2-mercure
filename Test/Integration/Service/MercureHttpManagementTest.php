<?php

declare(strict_types=1);

namespace MaxStan\Mercure\Test\Integration\Service;

use Magento\Framework\App\Response\Http;
use Magento\Framework\Stdlib\Cookie\SensitiveCookieMetadata;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use MaxStan\Mercure\Api\MercureHttpManagementInterface;
use MaxStan\Mercure\Service\MercureHttpManagement;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for Mercure HTTP header and cookie management.
 */
#[DbIsolation(true)]
class MercureHttpManagementTest extends TestCase
{
    /**
     * Verify Link header is set with correct hub URL.
     */
    #[Config('mercure/general/hub_url', 'https://hub.test/.well-known/mercure', 'store', 'default')]
    public function testAttachLinkHeaderSetsCorrectHeader(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $response = $objectManager->create(Http::class);

        $httpManagement = $objectManager->create(MercureHttpManagement::class, [
            'response' => $response,
        ]);

        $httpManagement->attachLinkHeader();

        $header = $response->getHeader('Link');
        $this->assertNotFalse($header);
        $this->assertSame(
            '<https://hub.test/.well-known/mercure>; rel="mercure"',
            $header->getFieldValue()
        );
    }

    /**
     * Verify authorization cookie is set with correct name, JWT value, and path.
     */
    #[Config('mercure/general/hub_url', 'https://hub.test/.well-known/mercure', 'store', 'default')]
    #[Config('mercure/general/jwt_secret', 'integration-test-secret-that-is-long-enough', 'store', 'default')]
    #[Config('mercure/general/jwt_algorithm', 'hmac.sha256', 'store', 'default')]
    public function testAttachAuthorizationCookieCallsWithCorrectArguments(): void
    {
        $objectManager = Bootstrap::getObjectManager();

        $mockCookieManager = $this->createMock(CookieManagerInterface::class);
        $mockCookieManager->expects($this->once())
            ->method('setSensitiveCookie')
            ->with(
                MercureHttpManagementInterface::AUTHORIZATION_COOKIE_NAME,
                $this->callback(function (string $jwt): bool {
                    return count(explode('.', $jwt)) === 3;
                }),
                $this->callback(function (SensitiveCookieMetadata $metadata): bool {
                    return $metadata->getPath() === 'https://hub.test/.well-known/mercure'
                        && $metadata->getSameSite() === 'Strict';
                })
            );

        $response = $objectManager->create(Http::class);

        $httpManagement = $objectManager->create(MercureHttpManagement::class, [
            'response' => $response,
            'cookieManager' => $mockCookieManager,
        ]);

        $httpManagement->attachAuthorizationCookie();
    }

    /**
     * Verify attach() sets both Link header and authorization cookie.
     */
    #[Config('mercure/general/hub_url', 'https://hub.test/.well-known/mercure', 'store', 'default')]
    #[Config('mercure/general/jwt_secret', 'integration-test-secret-that-is-long-enough', 'store', 'default')]
    #[Config('mercure/general/jwt_algorithm', 'hmac.sha256', 'store', 'default')]
    public function testAttachCallsBothHeaderAndCookie(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $response = $objectManager->create(Http::class);

        $mockCookieManager = $this->createMock(CookieManagerInterface::class);
        $mockCookieManager->expects($this->once())
            ->method('setSensitiveCookie');

        $httpManagement = $objectManager->create(MercureHttpManagement::class, [
            'response' => $response,
            'cookieManager' => $mockCookieManager,
        ]);

        $httpManagement->attach();

        $header = $response->getHeader('Link');
        $this->assertNotFalse($header, 'Link header should be set by attach()');
    }
}
