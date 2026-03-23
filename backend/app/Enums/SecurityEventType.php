<?php

declare(strict_types=1);

namespace App\Enums;

enum SecurityEventType: string
{
    case FailedLogin = 'failed_login';
    case PasswordChange = 'password_change';
    case AccountDeletion = 'account_deletion';
    case AdminAction = 'admin_action';
    case ForbiddenAccess = 'forbidden_access';
    case UnhandledException = 'unhandled_exception';
    case AiGenerationFailure = 'ai_generation_failure';
}
