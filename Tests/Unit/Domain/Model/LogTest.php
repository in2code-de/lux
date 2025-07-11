<?php

namespace In2code\Lux\Tests\Unit\Domain\Model;

use DateTime;
use In2code\Lux\Domain\Model\Log;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Tests\Helper\TestingHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(Log::class)]
#[CoversMethod(Log::class, 'canBeRead')]
#[CoversMethod(Log::class, 'getActionExecutionTime')]
#[CoversMethod(Log::class, 'getActionTitle')]
#[CoversMethod(Log::class, 'getCrdate')]
#[CoversMethod(Log::class, 'getEventName')]
#[CoversMethod(Log::class, 'getHref')]
#[CoversMethod(Log::class, 'getIdentifiedStatus')]
#[CoversMethod(Log::class, 'getPageUid')]
#[CoversMethod(Log::class, 'getProperties')]
#[CoversMethod(Log::class, 'getShortenerpath')]
#[CoversMethod(Log::class, 'getShownContentUid')]
#[CoversMethod(Log::class, 'getSite')]
#[CoversMethod(Log::class, 'getStatus')]
#[CoversMethod(Log::class, 'getVisitor')]
#[CoversMethod(Log::class, 'getWorkflowTitle')]
#[CoversMethod(Log::class, 'setCrdate')]
#[CoversMethod(Log::class, 'setProperties')]
#[CoversMethod(Log::class, 'setPropertiesArray')]
#[CoversMethod(Log::class, 'setSite')]
#[CoversMethod(Log::class, 'setStatus')]
class LogTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    public function setUp(): void
    {
        parent::setUp();
        TestingHelper::setDefaultConstants();
    }

    public function testVisitorGetterAndSetter(): void
    {
        $visitor = new Visitor();
        $log = new Log();
        $log->setVisitor($visitor);
        self::assertSame($visitor, $log->getVisitor());
    }

    public function testStatusGetterAndSetter(): void
    {
        $status = Log::STATUS_IDENTIFIED;
        $log = new Log();
        $log->setStatus($status);
        self::assertSame($status, $log->getStatus());
    }

    public function testCrdateGetterAndSetter(): void
    {
        $crdate = new DateTime('2023-01-01');
        $log = new Log();
        $log->setCrdate($crdate);
        self::assertSame($crdate, $log->getCrdate());
    }

    public function testPropertiesGetterAndSetter(): void
    {
        $properties = '{"key1":"value1","key2":"value2"}';
        $expectedArray = ['key1' => 'value1', 'key2' => 'value2'];
        $log = new Log();
        $log->setProperties($properties);
        self::assertSame($expectedArray, $log->getProperties());
    }

    public function testSetPropertiesArray(): void
    {
        $propertiesArray = ['key1' => 'value1', 'key2' => 'value2'];
        $log = new Log();
        $log->setPropertiesArray($propertiesArray);
        self::assertSame($propertiesArray, $log->getProperties());
    }

    public function testSiteGetterAndSetter(): void
    {
        $site = 'example';
        $log = new Log();
        $log->setSite($site);
        self::assertSame($site, $log->getSite());
    }

    public function testGetHref(): void
    {
        $href = '/test/page';
        $expectedHref = 'test/page';
        $log = new Log();
        $log->setPropertiesArray(['href' => $href]);
        self::assertSame($expectedHref, $log->getHref());
    }

    public function testGetWorkflowTitle(): void
    {
        $workflowTitle = 'Test Workflow';
        $log = new Log();
        $log->setPropertiesArray(['workflowTitle' => $workflowTitle]);
        self::assertSame($workflowTitle, $log->getWorkflowTitle());
    }

    public function testGetActionTitle(): void
    {
        $actionTitle = 'Test Action';
        $log = new Log();
        $log->setPropertiesArray(['actionTitle' => $actionTitle]);
        self::assertSame($actionTitle, $log->getActionTitle());
    }

    public function testGetActionExecutionTime(): void
    {
        $executionTime = 123;
        $log = new Log();
        $log->setPropertiesArray(['executionTime' => $executionTime]);
        self::assertSame($executionTime, $log->getActionExecutionTime());
    }

    public function testGetShownContentUid(): void
    {
        $contentUid = '42';
        $log = new Log();
        $log->setPropertiesArray(['shownContentUid' => $contentUid]);
        self::assertSame($contentUid, $log->getShownContentUid());
    }

    public function testGetPageUid(): void
    {
        $pageUid = '10';
        $log = new Log();
        $log->setPropertiesArray(['pageUid' => $pageUid]);
        self::assertSame($pageUid, $log->getPageUid());
    }

    public function testGetShortenerpath(): void
    {
        $path = 'abc123';
        $log = new Log();
        $log->setPropertiesArray(['path' => $path]);
        self::assertSame($path, $log->getShortenerpath());
    }

    public function testGetEventName(): void
    {
        $eventName = 'click';
        $log = new Log();
        $log->setPropertiesArray(['eventName' => $eventName]);
        self::assertSame($eventName, $log->getEventName());
    }

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

    public function testCanBeReadWithEmptySite(): void
    {
        $log = new Log();
        // When site is empty, canBeRead should return true regardless of backend/admin status
        self::assertTrue($log->canBeRead());
    }
}
