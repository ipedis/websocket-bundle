<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Symfony\Set\SymfonySetList;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\TypeDeclaration\Rector\Property\AddPropertyTypeDeclarationRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/Resources',
    ])
    ->withRules([
        InlineConstructorDefaultToPropertyRector::class,
        AnnotationToAttributeRector::class,
        AddPropertyTypeDeclarationRector::class,
        AddReturnTypeDeclarationRector::class,
    ])
    ->withPhpSets()
    ->withComposerBased(phpunit: true, symfony: true)
    ->withAttributesSets(symfony: true)
    ->withSets([
        SymfonySetList::SYMFONY_CODE_QUALITY,
    ])
;
