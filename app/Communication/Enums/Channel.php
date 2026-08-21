<?php

namespace App\Communication\Enums;

enum Channel: string
{
    case SMS = 'sms';
    case Email = 'email';
    case Notice = 'notice';
    case Call = 'call';
    case Meeting = 'meeting';

    public function isSystemGenerated(): bool
    {
        return in_array($this, [self::SMS, self::Email, self::Notice]);
    }

    public function isUserGenerated(): bool
    {
        return in_array($this, [self::Call, self::Meeting]);
    }

    public function label(): string
    {
        return match ($this) {
            self::SMS => 'SMS',
            self::Email => 'Email',
            self::Notice => 'Notice',
            self::Call => 'Phone Call',
            self::Meeting => 'Meeting',
        };
    }

    public function requiresContent(): bool
    {
        return in_array($this, [self::SMS, self::Email, self::Notice, self::Call]);
    }
}
