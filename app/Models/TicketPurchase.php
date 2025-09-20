<?php

declare(strict_types=1);

namespace App\Models;

use App\DbConnection;

// チケット購入に関する処理
class TicketPurchase
{
    // 「1カラム」から情報を１件取得
    public static function getby(string $col_name, string $value): array|false
    {
        $whitelist = [
            'email' => true,
            'token' => true,
        ];
        // カラム名のホワイトリストチェック
        if (false === isset($whitelist[$col_name])) {
            echo '不正なカラム名です';
            exit;
        }
        try {
            // $dbh = new \PDO($dsn, $db_config['user'], $db_config['pass'], $opt);
            $dbh = DbConnection::get();
            // プリペアドステートメント
            $sql = ("SELECT * FROM ticket_purchases WHERE {$col_name} = :value;");
            $pre = $dbh->prepare($sql);
            $pre->bindValue(':value', $value, \PDO::PARAM_STR);
            $pre->execute();
            $datum = $pre->fetch();
        } catch (\PDOException $e) {
            // XXX 暫定: 本来はlogに出力する & エラーページを出力する
            echo $e->getMessage();
            exit;
        }
        return $datum;
    }  

    // emailから情報を取得
    public static function getByEmail(string $email): array|false
    {
        return static::getby('email', $email);
    }

        // tokenから情報を取得
    public static function getByToken(string $token): array|false
    {
        return static::getby('token', $token);
    }
       

    // 全権取得
    public static function getAll(): array
    {
        try {
            // $dbh = new \PDO($dsn, $db_config['user'], $db_config['pass'], $opt);
            $dbh = DbConnection::get();
            // プリペアドステートメント
            $stmt = $dbh->prepare('SELECT * FROM ticket_purchases ORDER BY created_at DESC;');
            $stmt->execute();
            $list = $stmt->fetchAll();
        } catch (\PDOException $e) {
            // XXX 暫定: 本来はlogに出力する & エラーページを出力する
            echo $e->getMessage();
            exit;
        }
        return $list;
    }
}
