<?php

declare(strict_types=1);

namespace Bedriox\ExamplePlugin\Tests;

use JsonException;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
final class RepositoryFoundationTest extends TestCase
{
    /** @throws JsonException */
    public function testComposerMetadataKeepsThePackageIdentityAndLicense(): void
    {
        $contents = file_get_contents(__DIR__ . '/../composer.json');
        self::assertIsString($contents);

        $metadata = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);
        self::assertIsArray($metadata);
        self::assertArrayHasKey('require', $metadata);
        self::assertIsArray($metadata['require']);
        self::assertSame('bedriox/example-plugin', $metadata['name'] ?? null);
        self::assertSame('GPL-3.0-only', $metadata['license'] ?? null);
        self::assertSame('^8.4', $metadata['require']['php'] ?? null);
    }

    public function testApiManifestAndEntryPointArePublishedTogether(): void
    {
        $contents = file_get_contents(__DIR__ . '/../plugin.json');
        self::assertIsString($contents);
        $manifest = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);
        self::assertIsArray($manifest);
        self::assertSame(1, $manifest['schema'] ?? null);
        self::assertSame('ExamplePlugin', $manifest['name'] ?? null);
        self::assertSame('^0.1', $manifest['api'] ?? null);
        self::assertSame('Bedriox\\ExamplePlugin\\Main', $manifest['main'] ?? null);
        self::assertFileExists(__DIR__ . '/../src/Main.php');
    }
}
