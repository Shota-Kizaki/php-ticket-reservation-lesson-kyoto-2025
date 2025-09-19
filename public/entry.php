<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/initialize.php';

use App\DbConnection;
use App\Models\TicketPurchase;
use App\Models\TicketTokenUsage;

// tokenの把握
if ('' === ($token = strval($_GET['token'] ?? ''))) {
    // tokenがないのでinputに飛ばす
    header('Location: /index.php');
    exit;
}

/* tokenの確認 */
$datum = TicketPurchase::getByToken($token);

// なかったらエラー出力
if (false === $datum) {
    echo $twig->render('entry_error.twig');
    exit;
} else {
    // tokenがあったら、使用履歴を登録する
    TicketTokenUsage::consumeToken($token);
}

// 使用

// あったら最低限の情報表示
echo $twig->render('entry.twig', [
    'purchaser_name' => $datum['purchaser_name'],
    'quantity' => (int)$datum['quantity'],
]);
