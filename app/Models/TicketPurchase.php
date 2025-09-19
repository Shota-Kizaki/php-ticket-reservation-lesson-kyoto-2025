<?php

declare(strict_types=1);

namespace App\Models;

use App\DbConnection;

// チケット購入に関する処理
class TicketPurchase
{
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
