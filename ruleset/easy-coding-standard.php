<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\ClassNotation\OrderedClassElementsFixer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(__DIR__. '../vendor/sylius-labs/coding-standard/easy-coding-standard.yml');

    $services = $containerConfigurator->services();

    $services->set(OrderedClassElementsFixer::class);

    $parameters = $containerConfigurator->parameters();

    $parameters->set('paths', [__DIR__ . '/src', 'tests']);

    $parameters->set('exclude_files', ['tests/Application/**']);
};
