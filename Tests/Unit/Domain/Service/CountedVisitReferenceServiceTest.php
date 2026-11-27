<?php

declare(strict_types=1);

namespace In2code\Lux\Tests\Unit\Domain\Service;

use In2code\Lux\Domain\Model\Transfer\CountedVisitReference;
use In2code\Lux\Domain\Service\CountedVisitReferenceService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use TYPO3\CMS\Core\Crypto\HashService;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(CountedVisitReferenceService::class)]
class CountedVisitReferenceServiceTest extends UnitTestCase
{
    private const TABLE = 'tx_luxenterprise_domain_model_abpagevisit';
    private const OTHER_TABLE = 'tx_luxenterprise_domain_model_shortenervisit';
    private const NOW = 1800000000;

    private CountedVisitReferenceService $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'] = 'lux-unit-test-encryption-key';
        $this->subject = new CountedVisitReferenceService(new HashService());
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey']);
        parent::tearDown();
    }

    public function testACreatedReferenceResolvesToTheSameRecord(): void
    {
        $reference = $this->subject->createReference(new CountedVisitReference(self::TABLE, 123, self::NOW));

        self::assertEquals(
            new CountedVisitReference(self::TABLE, 123, self::NOW),
            $this->subject->resolveReference(self::TABLE, $reference, self::NOW + 10)
        );
    }

    public function testTheReferenceContainsNoSecret(): void
    {
        $reference = $this->subject->createReference(new CountedVisitReference(self::TABLE, 123, self::NOW));

        self::assertMatchesRegularExpression('~^123\.' . self::NOW . '\.[a-f0-9]{40}$~', $reference);
    }

    public static function manipulatedReferenceDataProvider(): array
    {
        return [
            'other uid' => [static fn (string $reference): string => '124' . substr($reference, 3)],
            'other crdate' => [static fn (string $reference): string => str_replace('.1800000000.', '.1800000001.', $reference)],
            'other hmac' => [static fn (string $reference): string => substr($reference, 0, -1) . (str_ends_with($reference, '0') ? '1' : '0')],
            'uppercase hmac' => [static fn (string $reference): string => strtoupper($reference)],
            'without hmac' => [static fn (string $reference): string => '123.' . self::NOW],
            'empty' => [static fn (string $reference): string => ''],
            'uid zero' => [static fn (string $reference): string => '0' . substr($reference, 3)],
            'appended text' => [static fn (string $reference): string => $reference . 'x'],
        ];
    }

    #[DataProvider('manipulatedReferenceDataProvider')]
    public function testManipulatedReferencesAreRejected(callable $manipulate): void
    {
        $reference = $this->subject->createReference(new CountedVisitReference(self::TABLE, 123, self::NOW));

        self::assertNull($this->subject->resolveReference(self::TABLE, $manipulate($reference), self::NOW));
    }

    public function testAReferenceIsRejectedForAnotherTable(): void
    {
        $reference = $this->subject->createReference(new CountedVisitReference(self::TABLE, 123, self::NOW));

        self::assertNull($this->subject->resolveReference(self::OTHER_TABLE, $reference, self::NOW));
    }

    public function testAReferenceIsRejectedForATableThatIsNotAllowed(): void
    {
        $reference = $this->subject->createReference(new CountedVisitReference('be_users', 1, self::NOW));

        self::assertNull($this->subject->resolveReference('be_users', $reference, self::NOW));
    }

    public static function lifetimeDataProvider(): array
    {
        return [
            'just created' => [self::NOW, true],
            'at the end of the lifetime' => [self::NOW + CountedVisitReferenceService::LIFETIME, true],
            'after the lifetime' => [self::NOW + CountedVisitReferenceService::LIFETIME + 1, false],
            'slightly in the future' => [self::NOW - 60, true],
            'far in the future' => [self::NOW - 61, false],
        ];
    }

    #[DataProvider('lifetimeDataProvider')]
    public function testAReferenceIsOnlyAcceptedWithinItsLifetime(int $now, bool $expected): void
    {
        $reference = $this->subject->createReference(new CountedVisitReference(self::TABLE, 123, self::NOW));

        self::assertSame($expected, $this->subject->resolveReference(self::TABLE, $reference, $now) !== null);
    }

    public function testALongerLifetimeCanBeGiven(): void
    {
        $reference = $this->subject->createReference(new CountedVisitReference(self::TABLE, 123, self::NOW));
        $now = self::NOW + CountedVisitReferenceService::LIFETIME + 1;

        self::assertNull($this->subject->resolveReference(self::TABLE, $reference, $now));
        self::assertNotNull($this->subject->resolveReference(self::TABLE, $reference, $now, 86400));
    }
}
