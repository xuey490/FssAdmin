<?php
// 命令：php vendor/bin/php-cs-fixer fix --dry-run --diff 
// --dry-run：仅检查问题，不实际修改文件
// --diff：显示具体的代码差异
// 自动修复代码风格问题 php-cs-fixer fix /path/to/code
// 如果不指定路径，默认处理当前目录

$header = <<<'EOF'
This file is part of FssPHP Framework.

@link     https://github.com/xuey490/project
@license  https://github.com/xuey490/project/blob/main/LICENSE

@Filename: %s
@Date: %s
@Developer: xuey863toy
@Email: xuey863toy@gmail.com
EOF;

// 生成当前日期，格式为YYYY-MM-DD
$currentDate = date('Y-m-d');

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12'                                  => true,
        '@Symfony'                               => true,
        '@DoctrineAnnotation'                    => true,
        '@PhpCsFixer'                            => true,
        'header_comment'                         => [
            'comment_type' => 'PHPDoc',
            'header'       => sprintf($header, '%filename%', $currentDate),
            'separate'     => 'both',
            'location'     => 'after_declare_strict',
        ],
        'array_syntax'                           => [
            'syntax' => 'short',
        ],
        'list_syntax'                            => [
            'syntax' => 'short',
        ],
        'concat_space'                           => [
            'spacing' => 'one',
        ],
        'blank_line_before_statement'            => [
            'statements' => [
                'declare',
            ],
        ],
        'general_phpdoc_annotation_remove'       => [
            'annotations' => [
                'author',
            ],
        ],
        'ordered_imports'                        => [
            'imports_order'  => [
                'class', 'function', 'const',
            ],
            'sort_algorithm' => 'alpha',
        ],
        'single_line_comment_style'              => [
            'comment_types' => [
            ],
        ],
        'yoda_style'                             => [
            'always_move_variable' => false,
            'equal'                => false,
            'identical'            => false,
        ],
        'phpdoc_align'                           => [
            'align' => 'vertical',
        ],
        'multiline_whitespace_before_semicolons' => [
            'strategy' => 'no_multi_line',
        ],
        'constant_case'                          => [
            'case' => 'lower',
        ],
        'binary_operator_spaces'                 => [
            'default' => 'align',
        ],
        'class_attributes_separation'            => true,
        'combine_consecutive_unsets'             => true,
        'declare_strict_types'                   => true,
        'array_indentation'                      => true,
        'linebreak_after_opening_tag'            => true,
        'lowercase_static_reference'             => true,
        'no_useless_else'                        => true,
        'no_unused_imports'                      => true,
        'trim_array_spaces'                      => true,
        'not_operator_with_successor_space'      => true,
        'not_operator_with_space'                => false,
        'ordered_class_elements'                 => true,
        'php_unit_strict'                        => false,
        'phpdoc_separation'                      => false,
        'single_quote'                           => true,
        'standardize_not_equals'                 => true,
        'multiline_comment_opening_closing'      => true,
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
                         ->exclude('public')
                         ->exclude('storage')
                         ->exclude('resource')
                         ->exclude('vendor')
                         ->in(__DIR__ . '/framework')
    )
    ->setCacheFile(__DIR__ . '/.php-cs-fixer.cache');