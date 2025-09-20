<?php

declare(strict_types=1);

namespace App\Domain\Validate;

class EmailValidatorSimple implements EmailValidatorInterface
{
    public function validate(string $email): false|string
    {
        // @が1つあり、前後に1文字以上あるか簡易チェック
        return preg_match('/^.+@.+$/', $email) ? $email : false;
    }
}
