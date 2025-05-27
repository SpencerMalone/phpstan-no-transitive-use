<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use SpencerMalone\NoTransitiveUse\Rules\NoTransitiveUseRule;
use PHPStan\Reflection\ReflectionProvider;

class NoTransitiveUseRuleTest extends TestCase
{
    public function testIsFileInPrimaryDependencies(): void
    {
        $reflectionProvider = $this->createMock(ReflectionProvider::class);
        $rule = new NoTransitiveUseRule($reflectionProvider);

        $filePath = 'vendor/foo/bar/src/ClassA.php';

        // Make isFileInPrimaryDependencies public for testing or use Reflection to access it
        $refMethod = new \ReflectionMethod($rule, 'isFileInPrimaryDependencies');
        $refMethod->setAccessible(true);

        // Simulate primary dependencies
        $refProperty = new \ReflectionProperty($rule, 'primaryDependencies');
        $refProperty->setAccessible(true);
        $refProperty->setValue($rule, ['vendor/foo/bar']);

        $result = $refMethod->invoke($rule, $filePath);
        $this->assertTrue($result);
    }

    public function testIsFileInTransitiveDependency(): void
    {
        $reflectionProvider = $this->createMock(ReflectionProvider::class);
        $rule = new NoTransitiveUseRule($reflectionProvider);

        $filePath = 'vendor/composer/../transitive/package/src/ClassB.php';

        // Make isFileInPrimaryDependencies public for testing or use Reflection to access it
        $refMethod = new \ReflectionMethod($rule, 'isFileInPrimaryDependencies');
        $refMethod->setAccessible(true);

        // Simulate primary dependencies
        $refProperty = new \ReflectionProperty($rule, 'primaryDependencies');
        $refProperty->setAccessible(true);
        $refProperty->setValue($rule, ['vendor/foo/bar']);

        $result = $refMethod->invoke($rule, $filePath);
        $this->assertFalse($result);
    }

    public function testIsFileNotInVendor(): void
    {
        $reflectionProvider = $this->createMock(ReflectionProvider::class);
        $rule = new NoTransitiveUseRule($reflectionProvider);

        $filePath = 'src/ClassC.php';

        // Make isFileInPrimaryDependencies public for testing or use Reflection to access it
        $refMethod = new \ReflectionMethod($rule, 'isFileInPrimaryDependencies');
        $refMethod->setAccessible(true);

        // Simulate primary dependencies
        $refProperty = new \ReflectionProperty($rule, 'primaryDependencies');
        $refProperty->setAccessible(true);
        $refProperty->setValue($rule, ['vendor/foo/bar']);

        $result = $refMethod->invoke($rule, $filePath);
        $this->assertTrue($result);
    }
} 