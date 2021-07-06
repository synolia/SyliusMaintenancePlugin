<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\ClassNotation\OrderedClassElementsFixer;
use PhpCsFixer\Fixer\Operator\BinaryOperatorSpacesFixer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(__DIR__ . '/../vendor/sylius-labs/coding-standard/ecs.php');

    $services = $containerConfigurator->services();

    $services->set(OrderedClassElementsFixer::class);
    $services->set(BinaryOperatorSpacesFixer::class);

    $parameters = $containerConfigurator->parameters();

    $parameters->set('paths', [
        __DIR__ . '/../src',
        __DIR__ . '/../tests/PHPUnit',
    ]);

    $parameters->set('exclude_files', ['tests/Application/**']);
};
