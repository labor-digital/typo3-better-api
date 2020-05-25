<?php

return PhpCsFixer\Config::create()
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR2' => true,
        'single_quote' => [
            'strings_containing_single_quote_chars' => true
        ],
        'array_syntax' => [
            'syntax' => 'short',
        ],
    ]);
