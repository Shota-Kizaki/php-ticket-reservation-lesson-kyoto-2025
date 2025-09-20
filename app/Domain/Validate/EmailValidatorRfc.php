<?php

declare(strict_types=1);

namespace App\Domain\Validate;

// RFCに準拠したメールアドレスのバリデーション
class EmailValidatorRfc
{
    public function validate(string $email): string|false
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
}
