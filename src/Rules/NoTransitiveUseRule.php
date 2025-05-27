<?php declare(strict_types=1);

namespace SpencerMalone\NoTransitiveUse\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Reflection\ReflectionProvider;

/**
 * @implements Rule<Node>
 */
class NoTransitiveUseRule implements Rule
{
    /** @var string[]|null */
    private static ?array $primaryDependencies = null;

    public function __construct(
    private ReflectionProvider $reflectionProvider
    ) {
    }

    public function getNodeType(): string
    {
        return Node::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $errors = [];

        // Check use statements
        if ($node instanceof Node\Stmt\Use_) {
            foreach ($node->uses as $use) {
                $class = $use->name->toString();
                if ($this->reflectionProvider->hasClass($class)) {
                    $classReflection = $this->reflectionProvider->getClass($class);
                    $filePath = $classReflection->getFileName();
                    if ($filePath !== null && !$this->isFileInPrimaryDependencies($filePath)) {
                        $errors[] = RuleErrorBuilder::message(sprintf(
                            'Using class %s (defined in %s) from a transitive dependency is not allowed.',
                            $class,
                            $filePath
                        ))
                            ->identifier('noTransitiveDependency')
                            ->build();
                    }
                }
            }
        }

        // Check fully qualified class names
        if ($node instanceof Node\Name\FullyQualified) {
            $class = $node->toString();
            if ($this->reflectionProvider->hasClass($class)) {
                $classReflection = $this->reflectionProvider->getClass($class);
                $filePath = $classReflection->getFileName();
                if ($filePath !== null && !$this->isFileInPrimaryDependencies($filePath)) {
                    $errors[] = RuleErrorBuilder::message(sprintf(
                        'Using class %s (defined in %s) from a transitive dependency is not allowed.',
                        $class,
                        $filePath
                    ))
                    ->identifier('noTransitiveDependency')
                    ->build();
                }
            }
        }

        return $errors;
    }

    /**
     * Check if a file path belongs to a primary dependency
     *
     * @param  string $filePath
     * @return bool
     */
    private function isFileInPrimaryDependencies(string $filePath): bool
    {
        // Check if the file is in vendor directory
        if (!str_contains($filePath, 'vendor/composer/..')) {
            return true;
        }

        // Get primary dependencies if not already cached
        if (self::$primaryDependencies === null) {
            self::$primaryDependencies = $this->getPrimaryDependencies();
        }

        // Extract vendor path from file path
        $vendorPath = $this->extractVendorPath($filePath);
        if ($vendorPath === null) {
            return true;
        }

        // Check if this vendor path is in primary dependencies
        return in_array($vendorPath, self::$primaryDependencies, true);
    }

    /**
     * Get primary dependencies from composer.json
     *
     * @return string[]
     */
    private function getPrimaryDependencies(): array
    {
        $composerJsonPath = getcwd().'/composer.json';
        if (!file_exists($composerJsonPath)) {
            return [];
        }

        $composerContents = file_get_contents($composerJsonPath);
        if ($composerContents === false) {
            return [];
        }

        $composerData = json_decode($composerContents, true);
        if (!is_array($composerData)) {
            return [];
        }

        $dependencies = [];

        // Get require dependencies
        if (isset($composerData['require']) && is_array($composerData['require'])) {
            foreach ($composerData['require'] as $package => $version) {
                if (strpos($package, '/') !== false) {
                    $dependencies[] = 'vendor/composer/..'.$package;
                }
            }
        }

        // Get require-dev dependencies
        if (isset($composerData['require-dev']) && is_array($composerData['require-dev'])) {
            foreach ($composerData['require-dev'] as $package => $version) {
                if (strpos($package, '/') !== false) {
                    $dependencies[] = 'vendor/composer/..'.$package;
                }
            }
        }

        return $dependencies;
    }

    private function extractVendorPath(string $filePath): ?string
    {
        if (preg_match('#vendor/composer/../([^/]+/[^/]+)#', $filePath, $matches)) {
            return 'vendor/composer/..'.$matches[1];
        }

        return null;
    }
}
