<?php

$finder = PhpCsFixer\Finder::create()
    ->in(['src', 'tests'])
;

$config = new PhpCsFixer\Config();
return $config
    // keep close to the Symfony standard but allow
    // alignment of keys/values in array definitions
    ->setRules([
        // keep close to the Symfony standard
        '@Symfony'               => true,

        // but force alignment of keys/values in array definitions
        'binary_operator_spaces' => [
            'operators' => [
                '=>' => 'align_single_space_minimal_by_scope',
                '='  => null,
            ],
        ],

        // this would otherwise separate annotations
        'phpdoc_separation'      => [
            'skip_unlisted_annotations' => true,
        ],
    ])
    ->setFinder($finder)
;
