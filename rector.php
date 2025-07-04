<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;
use Rector\Config\RectorConfig;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\PHPUnit\CodeQuality\Rector\Class_\PreferPHPUnitSelfCallRector;
use Rector\PHPUnit\CodeQuality\Rector\Class_\PreferPHPUnitThisCallRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Transform\Rector\Attribute\AttributeKeyToClassConstFetchRector;
use Rector\TypeDeclaration\Rector\ArrowFunction\AddArrowFunctionReturnTypeRector;

// @see https://getrector.com/blog/5-common-mistakes-in-rector-config-and-how-to-avoid-them
return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/src',
        __DIR__.'/tests',
    ])
    ->withParallel(200, 4)
    ->withComposerBased(
        doctrine: true,
        phpunit: true,
    )
    ->withSets([
        LevelSetList::UP_TO_PHP_84,
        SetList::CODE_QUALITY,
        SetList::TYPE_DECLARATION,

        // unwanted: splits IF statements to force returns
        // SetList::EARLY_RETURN,

        // verify changes, some are unwanted!
        // SetList::DEAD_CODE,

        DoctrineSetList::DOCTRINE_CODE_QUALITY,

        PHPUnitSetList::PHPUNIT_CODE_QUALITY,
        PHPUnitSetList::PHPUNIT_120,
    ])
    ->withRules([
        PreferPHPUnitSelfCallRector::class,
    ])
    ->withSkip([
        __DIR__ . '/tests/Fixtures/app',

        // mostly unnecessary as they are callbacks to array_filter etc.
        AddArrowFunctionReturnTypeRector::class,

        // replaces our (imported) Types::JSON with \Doctrine\DBAL\Types\Types::JSON
        AttributeKeyToClassConstFetchRector::class,

        // replaces null === $project with !$project instanceof Project
        FlipTypeControlToUseExclusiveTypeRector::class,

        // uses $this->assert... instead of self::assert
        // @see https://discourse.laminas.dev/t/this-assert-vs-self-assert/448
        PreferPHPUnitThisCallRector::class,
    ])
;
