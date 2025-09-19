<?php

declare(strict_types=1);

namespace App\Models;

use App\DbConnection;


class TicketTokenUsage
{
    // tokenから情報を取得
    public static function consumeToken(string $token): array|false
    {
        try {
            // $dbh = new \PDO($dsn, $db_config['user'], $db_config['pass'], $opt);
            $dbh = DbConnection::get();

            // トランザクション開始
            $dbh->beginTransaction();
            // プリペアドステートメント
            $stmt = $dbh->prepare('SELECT * FROM ticket_purchases WHERE token = :token FOR UPDATE;');
            $stmt->bindValue(':token', $token, \PDO::PARAM_STR);
            $stmt->execute();
            $tokenUsage = $stmt->fetch();
            // 使用済みかどうか
            if (false === $tokenUsage) {
                $dbh->rollBack();
                return false;
            }
            // 使用済みにする
            $dbh = DbConnection::get();
            // プリペアドステートメント
            $sql = 'INSERT INTO ticket_token_usages (token, created_at, updated_at) VALUES (:token, :created_at, :updated_at);';
            $pre = $dbh->prepare($sql);
            $now = date('Y-m-d H:i:s');
            $pre->bindValue(':token', $token, \PDO::PARAM_STR);
            $pre->bindValue(':created_at', $now, \PDO::PARAM_STR);
            $pre->bindValue(':updated_at', $now, \PDO::PARAM_STR);
            $res = $pre->execute();
            // コミット
            $dbh->commit();
        } catch (\PDOException $e) {
            // XXX 暫定: 本来はlogに出力する & エラーページを出力する
            echo $e->getMessage();
            exit;
        }
        return $datum;
    }
    
    public static function markAsUsed(string $token): bool
    {
        try {
            // $dbh = new \PDO($dsn, $db_config['user'], $db_config['pass'], $opt);
            
            $res = $pre->execute();
        } catch (\PDOException $e) {
            // XXX 暫定: 本来はlogに出力する & エラーページを出力する
            echo $e->getMessage();
            exit;
        }
        return $res;
    }
}