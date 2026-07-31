<?php

namespace Modules\LogReader\Domain\Enums;

enum LogLevel: string
{
    case Emergency = 'emergency';
    case Alert = 'alert';
    case Critical = 'critical';
    case Error = 'error';
    case Warning = 'warning';
    case Notice = 'notice';
    case Info = 'info';
    case Debug = 'debug';

    public function color(): string
    {
        return match ($this) {
            self::Emergency, self::Alert, self::Critical => 'red',
            self::Error => 'orange',
            self::Warning => 'yellow',
            self::Notice => 'blue',
            self::Info => 'green',
            self::Debug => 'gray',
        };
    }

    public function isError(): bool
    {
        return in_array($this, [self::Emergency, self::Alert, self::Critical, self::Error]);
    }
}
