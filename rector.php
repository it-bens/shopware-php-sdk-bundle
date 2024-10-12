<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Foreach_\SimplifyForeachToCoalescingRector;
use Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;
use Rector\CodeQuality\Rector\If_\ConsecutiveNullCompareReturnsToNullCoalesceQueueRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfNotNullReturnRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfNullableReturnRector;
use Rector\CodeQuality\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector;
use Rector\CodeQuality\Rector\NullsafeMethodCall\CleanupUnneededNullsafeOperatorRector;
use Rector\CodeQuality\Rector\Ternary\ArrayKeyExistsTernaryThenValueToCoalescingRector;
use Rector\CodingStyle\Rector\If_\NullableCompareToNullRector;
use Rector\Config\RectorConfig;
use Rector\EarlyReturn\Rector\Foreach_\ChangeNestedForeachIfsToEarlyContinueRector;
use Rector\EarlyReturn\Rector\If_\ChangeIfElseValueAssignToEarlyReturnRector;
use Rector\EarlyReturn\Rector\If_\ChangeOrIfContinueToMultiContinueRector;
use Rector\EarlyReturn\Rector\If_\RemoveAlwaysElseRector;
use Rector\EarlyReturn\Rector\Return_\PreparedValueToEarlyReturnRector;
use Rector\EarlyReturn\Rector\Return_\ReturnBinaryOrToEarlyReturnRector;
use Rector\EarlyReturn\Rector\StmtsAwareInterface\ReturnEarlyIfVariableRector;
use Rector\Php74\Rector\Property\RestoreDefaultNullToNullableTypePropertyRector;
use Rector\Php84\Rector\Param\ExplicitNullableParamTypeRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Symfony\Set\SymfonySetList;
use Rector\TypeDeclaration\Rector\BooleanAnd\BinaryOpNullableToInstanceofRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromSymfonySerializerRector;
use Rector\TypeDeclaration\Rector\Empty_\EmptyOnNullableObjectToInstanceOfRector;
use Rector\TypeDeclaration\Rector\StmtsAwareInterface\DeclareStrictTypesRector;
use Rector\TypeDeclaration\Rector\While_\WhileNullableToInstanceofRector;
use Rector\ValueObject\PhpVersion;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])

    ->withPhpVersion(PhpVersion::PHP_81)

    ->withSets([
        SymfonySetList::SYMFONY_64,
        SymfonySetList::SYMFONY_CODE_QUALITY,
        SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION,
        PHPUnitSetList::PHPUNIT_100,
        PHPUnitSetList::PHPUNIT_CODE_QUALITY,
    ])

    ->withImportNames(importShortClasses: false)

    ->withRules(
        [
            DeclareStrictTypesRector::class,

            ChangeIfElseValueAssignToEarlyReturnRector::class,
            ChangeNestedForeachIfsToEarlyContinueRector::class,
            ChangeOrIfContinueToMultiContinueRector::class,
            PreparedValueToEarlyReturnRector::class,
            RemoveAlwaysElseRector::class,
            ReturnBinaryOrToEarlyReturnRector::class,
            ReturnEarlyIfVariableRector::class,

            AddVoidReturnTypeWhereNoReturnRector::class,
            ArrayKeyExistsTernaryThenValueToCoalescingRector::class,
            CleanupUnneededNullsafeOperatorRector::class,
            ConsecutiveNullCompareReturnsToNullCoalesceQueueRector::class,
            FlipTypeControlToUseExclusiveTypeRector::class,
            IssetOnPropertyObjectToPropertyExistsRector::class,
            SimplifyForeachToCoalescingRector::class,
            SimplifyIfNotNullReturnRector::class,

            BinaryOpNullableToInstanceofRector::class,
            EmptyOnNullableObjectToInstanceOfRector::class,
            ExplicitNullableParamTypeRector::class,
            NullableCompareToNullRector::class,
            RestoreDefaultNullToNullableTypePropertyRector::class,
            SimplifyIfNullableReturnRector::class,
            WhileNullableToInstanceofRector::class,

            ReturnTypeFromSymfonySerializerRector::class,
        ]
    )

    ->withPreparedSets(
        deadCode: true,
        codeQuality: false,
        codingStyle: false,
        typeDeclarations: false,
        privatization: false,
        naming: false,
        instanceOf: false,
        earlyReturn: true,
        strictBooleans: true
    );
