<?php

namespace Modules\AuditLog\Domain\Enums;

enum AuditEvent: string
{
    case Created = 'created';
    case Updated = 'updated';
    case Deleted = 'deleted';
    case Restored = 'restored';
    case ForceDeleted = 'force_deleted';
    case Login = 'login';
    case Logout = 'logout';
    case Custom = 'custom';
}
