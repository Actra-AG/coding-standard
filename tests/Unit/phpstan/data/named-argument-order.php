<?php

/**
 * @copyright Actra AG - https://www.actra.ch
 * @license   MIT
 */

declare(strict_types=1);

// Fixture of NamedArgumentOrderRuleTest, with deliberately wrong argument orders (excluded in phpstan.neon)

namespace actra\codingStandard\tests\Unit\phpstan\data;

function examples(Mailer $mailer, ?Mailer $optionalMailer, object $unknown): void
{
    // Correct order, also with omitted optional parameters and with positional arguments first
    $mailer->send(to: 'a', subject: 'b', body: 'c');
    $mailer->send(to: 'a', body: 'c');
    $mailer->send('a', subject: 'b', body: 'c');
    Mailer::create(host: 'a', tls: true);
    new Mailer(host: 'a', port: 1, tls: true);
    format(text: 'a', break: ' ');
    str_pad(string: 'a', length: 3, pad_string: ' ');

    // Wrong order
    $mailer->send(subject: 'b', to: 'a');
    $mailer->send('a', body: 'c', subject: 'b');
    $optionalMailer?->send(body: 'c', to: 'a');
    Mailer::create(tls: true, host: 'a');
    new Mailer(
        tls: true,
        host: 'a',
    );
    format(width: 10, text: 'a');
    str_pad(length: 3, string: 'a');

    // Skipped: unresolvable calls, spread arguments, named arguments collected by a variadic parameter
    $unknown->send(subject: 'b', to: 'a');
    unknownFunction(b: 1, a: 2);
    new UnknownClass(b: 1, a: 2);
    $mailer->send(...['subject' => 'b', 'to' => 'a']);
    $mailer->log(level: 'info', user: 'a', action: 'b');
    $mailer->log(user: 'a', level: 'info');
}
