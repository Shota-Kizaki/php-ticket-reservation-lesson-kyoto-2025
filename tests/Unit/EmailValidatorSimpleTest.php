<?php

declare(strict_types=1);

use App\Domain\Validate\EmailValidatorSimple;
use PHPUnit\Framework\TestCase;

final class EmailValidatorSimpleTest extends TestCase
{
    public function testOK(): void
    {
        $ok_data = [
            'a@b',
            'a@b.c',
 
        ];

        $validator = new EmailValidatorSimple();
        foreach ($ok_data as $email) {
        $r = $validator->validate($email);

            $this->assertNotSame(false, $r);
            // $this->assertSame($email, $r);
        }
    }

    public function testNG(): void
    {
        $ng_data = [

        ];
        $validator = new EmailValidatorSimple();
        foreach ($ng_data as $email) {
            $r = $validator->validate($email);
            $this->assertSame(false, $r, "Falsed: {$email}");
        }
    }
}