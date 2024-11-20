<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

return (new Config())
    ->setParallelConfig(ParallelConfigFactory::detect())
    ->setUsingCache(true)
    ->setRiskyAllowed(true)
    ->setRules([
        '@PhpCsFixer' => true,
        '@DoctrineAnnotation' => true,
        '@PHP82Migration' => true,
        '@PHP80Migration:risky' => true,

        'global_namespace_import' => true,
    ])
    ->setFinder(
        (new Finder())
            ->in(__DIR__)
            ->append([__FILE__])
            ->ignoreDotFiles(true)
            ->ignoreVCS(true)
            ->exclude('vendor')
            ->exclude('var')
    )
    ;
