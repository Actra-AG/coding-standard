<?php

/**
 * @copyright Actra AG - https://www.actra.ch
 * @license   MIT
 */

declare(strict_types=1);

use PhpCsFixer\Config\RuleCustomisationPolicyInterface;
use PhpCsFixer\Fixer\Comment\HeaderCommentFixer;

/**
 * File header of the Actra coding standard for the .php-cs-fixer.dist.php of the project (see templates/).
 * Files with code adapted from a third-party library get its license instead (see standards/php.md, section 2).
 *
 * $thirdParty: one entry per adapted folder; `contains` limits it to files containing that text, for folders that
 * mix own and adapted code, e.g. ['path' => '/src/mailer/', 'license' => 'LGPL-2.1-only', 'contains' => 'PHPMailer'].
 *
 * @return Closure(string, list<array{path: string, license: string, contains?: string}>): array{
 *     rule: array{header: string, comment_type: string, location: string, separate: string},
 *     policy: RuleCustomisationPolicyInterface,
 * }
 */
return static function (string $license, array $thirdParty): array {
    $rule = static fn(string $license): array => [
        'header' => "@copyright Actra AG - https://www.actra.ch\n@license   " . $license,
        'comment_type' => 'PHPDoc',
        'location' => 'after_open',
        'separate' => 'both',
    ];
    $policy = new readonly class ($thirdParty, $rule) implements RuleCustomisationPolicyInterface {
        /**
         * @param list<array{path: string, license: string, contains?: string}> $thirdParty
         * @param Closure(string): array{header: string, comment_type: string, location: string, separate: string} $rule
         */
        public function __construct(
            private array $thirdParty,
            private Closure $rule,
        ) {
        }

        public function getPolicyVersionForCache(): string
        {
            return hash(
                algo: 'xxh128',
                data: hash_file(algo: 'xxh128', filename: __FILE__) . json_encode(value: $this->thirdParty),
            );
        }

        public function getRuleCustomisers(): array
        {
            return [
                'header_comment' => function (SplFileInfo $file): bool|HeaderCommentFixer {
                    $path = str_replace(search: '\\', replace: '/', subject: $file->getPathname());
                    foreach ($this->thirdParty as $entry) {
                        if (!str_contains(haystack: $path, needle: $entry['path'])) {
                            continue;
                        }
                        if (array_key_exists(key: 'contains', array: $entry)) {
                            $content = (string) file_get_contents(filename: $path);
                            if (!str_contains(haystack: $content, needle: $entry['contains'])) {
                                continue;
                            }
                        }
                        $fixer = new HeaderCommentFixer();
                        $fixer->configure(($this->rule)($entry['license']));

                        return $fixer;
                    }

                    return true;
                },
            ];
        }
    };

    return ['rule' => $rule($license), 'policy' => $policy];
};
