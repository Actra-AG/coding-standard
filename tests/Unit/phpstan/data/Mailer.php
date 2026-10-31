<?php

/**
 * @copyright Actra AG - https://www.actra.ch
 * @license   MIT
 */

declare(strict_types=1);

namespace actra\codingStandard\tests\Unit\phpstan\data;

// Fixture of NamedArgumentOrderRuleTest

final readonly class Mailer
{
    public function __construct(
        public string $host,
        public int $port = 25,
        public bool $tls = false,
    ) {}

    public static function create(string $host, int $port = 25, bool $tls = false): Mailer
    {
        return new Mailer(host: $host, port: $port, tls: $tls);
    }

    public function send(string $to, string $subject, string $body = ''): void {}

    public function log(string $level, string ...$context): void {}
}
