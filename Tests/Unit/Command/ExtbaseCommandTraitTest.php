<?php

namespace In2code\Lux\Tests\Unit\Command;

use In2code\Lux\Command\ExtbaseCommandTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Console\Command\Command;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(ExtbaseCommandTrait::class)]
#[CoversMethod(ExtbaseCommandTrait::class, 'configureRootPageIdOption')]
class ExtbaseCommandTraitTest extends UnitTestCase
{
    public static function commandClassNameDataProvider(): array
    {
        $classNames = [];
        foreach ((array)glob(__DIR__ . '/../../../Classes/Command/*Command.php') as $fileName) {
            $className = 'In2code\\Lux\\Command\\' . basename($fileName, '.php');
            if (in_array(ExtbaseCommandTrait::class, class_uses($className), true)) {
                $classNames[$className] = [$className];
            }
        }
        return $classNames;
    }

    #[DataProvider('commandClassNameDataProvider')]
    public function testCommandOffersRootPageIdOption(string $className): void
    {
        /** @var Command $command */
        $command = new $className('lux:test');
        $optionName = $className::ROOT_PAGE_ID_OPTION_NAME;
        $definition = $command->getDefinition();
        self::assertTrue($definition->hasOption($optionName));
        $option = $definition->getOption($optionName);
        self::assertSame(0, $option->getDefault());
        self::assertTrue($option->isValueRequired());
    }
}
