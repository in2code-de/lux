<?php

namespace In2code\Lux\Tests\Unit\Domain\Model;

use DateTime;
use In2code\Lux\Domain\Model\Log;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Tests\Helper\TestingHelper;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @coversDefaultClass \In2code\Lux\Domain\Model\Log
 */
class LogTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    public function setUp(): void
    {
        parent::setUp();
        TestingHelper::setDefaultConstants();
    }

    /**
     * @covers ::getVisitor
     * @covers ::setVisitor
     */
    public function testVisitorGetterAndSetter(): void
    {
        $visitor = new Visitor();
        $log = new Log();
        $log->setVisitor($visitor);
        self::assertSame($visitor, $log->getVisitor());
    }

    /**
     * @covers ::getStatus
     * @covers ::setStatus
     */
    public function testStatusGetterAndSetter(): void
    {
        $status = Log::STATUS_IDENTIFIED;
        $log = new Log();
        $log->setStatus($status);
        self::assertSame($status, $log->getStatus());
    }

    /**
     * @covers ::getCrdate
     * @covers ::setCrdate
     */
    public function testCrdateGetterAndSetter(): void
    {
        $crdate = new DateTime('2023-01-01');
        $log = new Log();
        $log->setCrdate($crdate);
        self::assertSame($crdate, $log->getCrdate());
    }

    /**
     * @covers ::getProperties
     * @covers ::setProperties
     */
    public function testPropertiesGetterAndSetter(): void
    {
        $properties = '{"key1":"value1","key2":"value2"}';
        $expectedArray = ['key1' => 'value1', 'key2' => 'value2'];
        $log = new Log();
        $log->setProperties($properties);
        self::assertSame($expectedArray, $log->getProperties());
    }

    /**
     * @covers ::setPropertiesArray
     * @covers ::getProperties
     */
    public function testSetPropertiesArray(): void
    {
        $propertiesArray = ['key1' => 'value1', 'key2' => 'value2'];
        $log = new Log();
        $log->setPropertiesArray($propertiesArray);
        self::assertSame($propertiesArray, $log->getProperties());
    }

    /**
     * @covers ::getSite
     * @covers ::setSite
     */
    public function testSiteGetterAndSetter(): void
    {
        $site = 'example';
        $log = new Log();
        $log->setSite($site);
        self::assertSame($site, $log->getSite());
    }

    /**
     * @covers ::getHref
     */
    public function testGetHref(): void
    {
        $href = '/test/page';
        $expectedHref = 'test/page';
        $log = new Log();
        $log->setPropertiesArray(['href' => $href]);
        self::assertSame($expectedHref, $log->getHref());
    }

    /**
     * @covers ::getWorkflowTitle
     */
    public function testGetWorkflowTitle(): void
    {
        $workflowTitle = 'Test Workflow';
        $log = new Log();
        $log->setPropertiesArray(['workflowTitle' => $workflowTitle]);
        self::assertSame($workflowTitle, $log->getWorkflowTitle());
    }

    /**
     * @covers ::getActionTitle
     */
    public function testGetActionTitle(): void
    {
        $actionTitle = 'Test Action';
        $log = new Log();
        $log->setPropertiesArray(['actionTitle' => $actionTitle]);
        self::assertSame($actionTitle, $log->getActionTitle());
    }

    /**
     * @covers ::getActionExecutionTime
     */
    public function testGetActionExecutionTime(): void
    {
        $executionTime = 123;
        $log = new Log();
        $log->setPropertiesArray(['executionTime' => $executionTime]);
        self::assertSame($executionTime, $log->getActionExecutionTime());
    }

    /**
     * @covers ::getShownContentUid
     */
    public function testGetShownContentUid(): void
    {
        $contentUid = '42';
        $log = new Log();
        $log->setPropertiesArray(['shownContentUid' => $contentUid]);
        self::assertSame($contentUid, $log->getShownContentUid());
    }

    /**
     * @covers ::getPageUid
     */
    public function testGetPageUid(): void
    {
        $pageUid = '10';
        $log = new Log();
        $log->setPropertiesArray(['pageUid' => $pageUid]);
        self::assertSame($pageUid, $log->getPageUid());
    }

    /**
     * @covers ::getShortenerpath
     */
    public function testGetShortenerpath(): void
    {
        $path = 'abc123';
        $log = new Log();
        $log->setPropertiesArray(['path' => $path]);
        self::assertSame($path, $log->getShortenerpath());
    }

    /**
     * @covers ::getEventName
     */
    public function testGetEventName(): void
    {
        $eventName = 'click';
        $log = new Log();
        $log->setPropertiesArray(['eventName' => $eventName]);
        self::assertSame($eventName, $log->getEventName());
    }

    /**
     * @covers ::getIdentifiedStatus
     */
    public function testGetIdentifiedStatus(): void
    {
        $identifiedStatus = Log::getIdentifiedStatus();
        self::assertIsArray($identifiedStatus);
        self::assertContains(Log::STATUS_IDENTIFIED, $identifiedStatus);
        self::assertContains(Log::STATUS_IDENTIFIED_FORMLISTENING, $identifiedStatus);
        self::assertContains(Log::STATUS_IDENTIFIED_LUXLETTERLINK, $identifiedStatus);
        self::assertContains(Log::STATUS_IDENTIFIED_FRONTENDAUTHENTICATION, $identifiedStatus);
        self::assertContains(Log::STATUS_IDENTIFIED_EMAIL4LINK, $identifiedStatus);
    }

    /**
     * @covers ::canBeRead
     */
    public function testCanBeReadWithEmptySite(): void
    {
        $log = new Log();
        // When site is empty, canBeRead should return true regardless of backend/admin status
        self::assertTrue($log->canBeRead());
    }
}
