# PHPStan No Transitive Use

A PHPStan extension that disallows using classes from transitive dependencies.

## Installation

```bash
composer require --dev spencermalone/phpstan-no-transitive-use
```

## Usage

The extension will automatically be loaded when you run PHPStan. It will check all `use` statements in your code and report an error if you're trying to use a class from a transitive dependency.

For example, if your project depends on package `A` which depends on package `B`, and you try to use a class from package `B` directly, PHPStan will report an error.

## Configuration

If you do not use phpstan/extension-installer, you need to add:

```
includes:
	- vendor/spencermalone/phpstan-no-transitive-use/extension.neon
```

to your phpstan.neon

No additional configuration is needed if you use phpstan/extension-installer. 

The extension automatically reads your `composer.json` to determine which dependencies are primary (directly required) and which are transitive.

## Example

```php
// This will cause an error if 'vendor/transitive/package' is not in your composer.json
use Transitive\Package\SomeClass;
```

## License

MIT 