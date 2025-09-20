<?php

declare(strict_types=1);

use App\Domain\Validate\EmailValidatorRfc;
use PHPUnit\Framework\TestCase;

final class EmailValidatorRfcTest extends TestCase
{
    public function testOK(): void
    {
        $ok_data = [
            'simple@example.com',                      // ごく普通
            'very.common@example.com',                 // ドットあり
            'disposable.style.email.with+symbol@example.com', // +あり(拡張メールアドレスでよく使われる)
            'other.email-with-hyphen@example.com',     // ハイフン
            'fully-qualified-domain@example.co.jp',    // サブドメイン
            'user.name+tag+sorting@example.com',       // タグつき
            '"quoted@(local)"@example.com',             // クォート付きローカル部
            '1234567890@example.com',                  // 数字のみローカル部
            str_repeat('a', 64) . '@example.com',       // ローカル部が64文字
            'email@[123.123.123.123]',                   // ドメイン部がIPアドレス
 
        ];

        $validator = new EmailValidatorRfc();
        foreach ($ok_data as $email) {
            $r = $validator->validate($email);
            $this->assertNotSame(false, $r, "Failed: {$email}");
            // $this->assertSame($email, $r);
        }
    }

    public function testNG(): void
    {
        $ng_data = [
            'plainaddress',                            // @なし
            '@no-local-part.com',                      // ローカル部なし
            'Outlook Contact <outlook-contact@domain.com>', // display-name付き
            'user..doubledot@example.com',              // ローカル部で連続ドット
            'user.@example.com',                       // ローカル部末尾ドット
            'user@.example.com',                       // ドメイン部先頭ドット
            'user@example..com',                       // ドメイン部で連続ドット
            str_repeat('a', 65) . '@example.com',       // ローカル部が64文字超え
            'user@' . str_repeat('a', 253) . '.com',    // ドメイン部が253文字超え
            str_repeat('a', 64) . '@' . str_repeat('a', 240) . '.jp',    // 全体の長さが254文字超え
 
        ];
        $validator = new EmailValidatorRfc();
        foreach ($ng_data as $email) {
            $r = $validator->validate($email);
            $this->assertSame(false, $r, "Falsed: {$email}");
        }
    }
}