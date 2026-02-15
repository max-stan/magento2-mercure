<?php
declare(strict_types=1);

namespace MaxStan\Mercure\Test\Integration\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\TestFramework\Helper\Bootstrap;
use MaxStan\Mercure\ViewModel\Mercure;
use PHPUnit\Framework\TestCase;

/**
 * Integration test for Mercure ViewModel
 *
 * @magentoAppArea frontend
 */
class MercureTest extends TestCase
{
    private Mercure $mercure;

    protected function setUp(): void
    {
        parent::setUp();
        $objectManager = Bootstrap::getObjectManager();
        $this->mercure = $objectManager->create(Mercure::class);
    }

    public function testGetHubUrlContainsMercureEndpoint(): void
    {
        $hubUrl = $this->mercure->getHubUrl();

        $this->assertStringContainsString(
            '.well-known/mercure',
            $hubUrl,
            'Hub URL should contain the Mercure endpoint path'
        );
    }

    public function testGetHubUrlDoesNotEndWithSlash(): void
    {
        $hubUrl = $this->mercure->getHubUrl();

        $this->assertStringEndsNotWith(
            '/',
            $hubUrl,
            'Hub URL should not end with a trailing slash'
        );
    }

    public function testImplementsArgumentInterface(): void
    {
        $this->assertInstanceOf(
            ArgumentInterface::class,
            $this->mercure,
            'Mercure ViewModel must implement ArgumentInterface to be used in templates'
        );
    }

    public function testGetHubUrlReturnsNonEmptyString(): void
    {
        $hubUrl = $this->mercure->getHubUrl();

        $this->assertNotEmpty(
            $hubUrl,
            'Hub URL should not be empty'
        );
        $this->assertIsString(
            $hubUrl,
            'Hub URL should be a string'
        );
    }

    public function testGetHubUrlIsValidUrl(): void
    {
        $hubUrl = $this->mercure->getHubUrl();

        $this->assertMatchesRegularExpression(
            '/^https?:\/\/.+/',
            $hubUrl,
            'Hub URL should be a valid HTTP/HTTPS URL'
        );
    }
}
