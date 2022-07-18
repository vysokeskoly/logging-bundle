<?php

declare(strict_types=1);

use PHP_CodeSniffer\Standards\Generic\Sniffs\PHP\ForbiddenFunctionsSniff;
use PhpCsFixer\Fixer\Alias\MbStrFunctionsFixer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\EasyCodingStandard\ValueObject\Option;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(
        Option::SKIP,
        [
            MbStrFunctionsFixer::class => ['src/Monolog/Handler/GraphiteHandler.php'],
            ForbiddenFunctionsSniff::class => [
                'src/Monolog/Formatter/ExtendedFormatter.php',
                'src/Monolog/Formatter/Gelf/MessageFormatter.php',
            ],
        ],
    );

    $containerConfigurator->import(__DIR__ . '/tools/coding-standards/vendor/lmc/coding-standard/ecs.php');
    $containerConfigurator->import(__DIR__ . '/tools/coding-standards/vendor/lmc/coding-standard/ecs-8.1.php');
};
