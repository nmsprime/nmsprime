<?php

$finder = PhpCsFixer\Finder::create()
    ->name('*.php')
    ->notName('*.blade.php')
    ->notPath('public')
    ->notPath('vendor')
    ->notPath('storage')
    ->notPath('bootstrap/cache')
    ->notPath('/[Dd]atabase/')
    ->notPath('/[Rr]esources\/lang/')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true)
    ->in(__DIR__);

return PhpCsFixer\Config::create()
    ->setRules([
    'psr0' => false,
    '@PSR2' => true,
    '@Symfony' => true,
    'align_multiline_comment' => true,
    'array_syntax' => [
        'syntax' => 'short'
    ],
    'binary_operator_spaces' => [
        'default' => 'single_space',
        'operators' => [
            '=>' => 'align_single_space_minimal'
        ]
    ],
    'increment_style' => [
        'style' => 'post'
    ],
    'not_operator_with_successor_space' => true,
    'method_argument_space' => [
        'on_multiline' => 'ensure_fully_multiline'
    ],
    'single_class_element_per_statement' => [
        'elements' => ['property'],
    ],
    'phpdoc_no_empty_return' => false,
    'phpdoc_summary' => false,
    'yoda_style' => false,
    ])
    ->setIndent("    ")
    ->setFinder($finder);
