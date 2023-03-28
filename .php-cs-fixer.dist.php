<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude('bin')
    ->exclude('config')
    ->exclude('public')
    ->exclude('templates')
    ->exclude('var')
    ->exclude('vendor')
    ->in(__DIR__)
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        '@PHP81Migration' => true,
        'array_syntax' => ['syntax' => 'short'],
        'declare_strict_types' => true,
        'ordered_class_elements' => [
            'order' => [
                'use_trait', 'constant_public', 'constant_protected', 'constant_private',
                'property_static', 'property_public', 'property_protected', 'property_private',
                'construct', 'destruct', 'magic',
                'method_public_static', 'method_public_abstract_static', 'method_public', 'method_public_abstract',
                'method_protected_static', 'method_protected_abstract_static', 'method_protected', 'method_protected_abstract',
                'method_private_static', 'method_private'
            ],
            'sort_algorithm' => 'none'
        ],
        'ordered_imports' => ['sort_algorithm' => 'alpha', 'imports_order' => ['class', 'function', 'const']],
        'multiline_whitespace_before_semicolons' => ['strategy' => 'new_line_for_chained_calls'],
        'phpdoc_line_span' => ['const' => 'single', 'method' => 'multi', 'property' => 'single'],
        'single_line_throw' => false,
        'php_unit_method_casing' => false
    ])
    ->setFinder($finder)
    ;