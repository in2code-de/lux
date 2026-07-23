<?php

namespace In2code\Lux\Tests\Functional\Domain\Repository;

use In2code\Lux\Domain\Model\Company;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Tests\Functional\Fixtures\Domain\Repository\AbstractRepositoryAccessor;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Proves end to end (against a real database, using the real driver based quoting) that filter values are quoted
 * safely: legitimate values still match and injection payloads can neither break out of the query nor match every row.
 */
class AbstractRepositoryTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = ['typo3conf/ext/lux'];

    protected AbstractRepositoryAccessor $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = new AbstractRepositoryAccessor();
        $connection = $this->getConnectionPool()->getConnectionForTable(Company::TABLE_NAME);
        $connection->insert(
            Company::TABLE_NAME,
            ['size_class' => '04', 'revenue_class' => '05', 'domain' => 'in2code.de', 'description' => '']
        );
        $connection->insert(
            Company::TABLE_NAME,
            ['size_class' => '07', 'revenue_class' => '08', 'domain' => 'typo3.org', 'description' => '']
        );
    }

    private function countCompanies(string $whereClause): int
    {
        $connection = $this->getConnectionPool()->getConnectionForTable(Company::TABLE_NAME);
        $sql = 'select count(*) from ' . Company::TABLE_NAME . ' c where c.deleted=0' . $whereClause;
        return (int)$connection->executeQuery($sql)->fetchOne();
    }

    public function testLegitimateZeroPaddedSizeClassMatchesExactRow(): void
    {
        $filter = new FilterDto();
        $filter->setSizeClass('04');
        self::assertSame(1, $this->countCompanies($this->subject->buildSizeClassClause($filter, 'c')));
    }

    /**
     * "0\" or 1=1" would return every row if it were injected unquoted (11 is truthy). With driver based quoting the
     * sanitized value is compared as a literal string, so it matches no row and the query does not error.
     */
    public function testSizeClassInjectionPayloadMatchesNoRowAndDoesNotError(): void
    {
        $filter = new FilterDto();
        $filter->setSizeClass('0" or 1=1');
        self::assertSame(0, $this->countCompanies($this->subject->buildSizeClassClause($filter, 'c')));
    }

    /**
     * A trailing backslash is the classic way to escape the closing quote and break out of a double quoted literal.
     * sanitizeString removes it and the driver quotes the remainder, so the query stays valid and matches no row.
     */
    public function testSizeClassBackslashBreakoutIsNeutralised(): void
    {
        $filter = new FilterDto();
        $filter->setSizeClass('0\\');
        self::assertSame(0, $this->countCompanies($this->subject->buildSizeClassClause($filter, 'c')));
    }

    public function testLegitimateDomainMatchesExactRow(): void
    {
        $filter = new FilterDto();
        $filter->setDomain('in2code.de');
        self::assertSame(1, $this->countCompanies($this->subject->buildDomainClause($filter, 'c')));
    }
}
