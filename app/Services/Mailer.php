<?php

declare(strict_types=1);

namespace App\Services;

class Mailer
{
    public static function send(string $to, string $subject, string $body, string $replyTo = ''): bool
    {
        if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $headers = "Content-Type: text/plain; charset=UTF-8\r\n";
        $headers .= "From: Tandlab website <no-reply@tandlab.nl>\r\n";
        if ($replyTo !== '' && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
            $headers .= 'Reply-To: ' . $replyTo . "\r\n";
        }

        return @mail($to, $subject, $body, $headers);
    }
}
