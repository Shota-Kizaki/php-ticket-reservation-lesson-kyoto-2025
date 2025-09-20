<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/initialize.php';

use App\Config;
use App\DbConnection;
use App\Models\TicketPurchase;


// 入力を受け取る
// [TODO] POSTからname/email/quantityを受け取る
// [TODO] 受け取ったデータは、$input 変数に連想配列の形で格納する
$input = [
    'purchaser_name' => $_POST["purchaser_name"] ?? '', // 氏名
    'email' => $_POST["email"] ?? '',          // メアド
    'quantity' => $_POST["quantity"] ?? '',       // チケットの枚数
];

// 先生のコードを参考
// $parms = ["purchaser_name", "email", "quantity"];
// foreach ($parms as $p) {
//     if (!isset($input[$p])) {
//         $input[$p] = '';
//     }
// };

/* validate */
$errord = [];
// 氏名の入力
if ($input['purchaser_name'] === '') {
    $errord['purchaser_name'] = '氏名を入力してください';
}

// メアドの確認
// [TODO] emailが「空でないこと」「emailのフォーマットとして適切であること」の確認
if ($input['email'] === '') {
    $errord['email'] = 'メールアドレスを入力してください';
} else {
    $email = $event_policy->emailValidate($input['email']);
    // メアドの重複チェック
    $allow_duplicate_email = Config::get('event')['allow_duplicate_email'] ?? false; //デフォルト不許可
    if (false === filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        $errord['email'] = 'メールアドレスの形式が正しくありません';
    } elseif (false === $allow_duplicate_email) {
        // 重複チェックを行う
        $ticket = TicketPurchase::getByEmail($email);
        if (false !== $ticket) {
            $errord['email'] = 'このメールアドレスは既に使われています';
        }
    }
}

// チケットの枚数
// [TODO] quantityが「空でないこと」「整数であること」の確認
if ($input['quantity'] === '') {
    $errord['quantity'] = 'チケットの枚数を入力してください';
} else {
    $quantity = filter_var($input['quantity'], FILTER_VALIDATE_INT);
    if (false === $quantity){
        $errord['quantity'] = 'チケットの枚数は整数で入力してください';
    } elseif (0 >= filter_var($input['quantity'], FILTER_VALIDATE_INT)) {
        $errord['quantity'] = 'チケットの枚数は1以上で入力してください';
    } elseif (false === $event_policy->canReserveQuantity(intval($input["quantity"]))) {
        $max = Config::get('event')['quantity_policy']['options']['max_per_order'];
        $errord['quantity'] = "チケット枚数は{$max}枚以内でお願いします";
    }
}

// エラーがあった場合、入力フォームに戻す
if (count($errord) > 0) {
    // セッションにエラー内容と入力値を保存しておく
    $_SESSION['errord'] = $errord;
    $_SESSION['input'] = $input;
    // 入力フォームに戻す
    header('Location: index.php');
    exit;
}

// tokenの作成
// [TODO] 「推測不能文字列」として適切なtokenを生成し、$token 変数に格納する
$token = bin2hex(random_bytes(16));

/* DBへの登録 */
// DB接続情報
try {
    // $dbh = new \PDO($dsn, $db_config['user'], $db_config['pass'], $opt);
    $dbh = DbConnection::get();
} catch (\PDOException $e) {
    // XXX 暫定: 本来はlogに出力する & エラーページを出力する
    echo $e->getMessage();
    exit;
}

try {
    // データの登録
    // [TODO] ticket_purchases テーブルに登録する
    // プリペアードステートメントを作る
    $sql = 'INSERT INTO ticket_purchases (email, purchaser_name, quantity, token, created_at, updated_at)
     VALUES (:email, :purchaser_name, :quantity, :token, :created_at, :updated_at)';
    $pre = $dbh->prepare($sql);
    // プレースホルダーに値をバインドする
    $now = date('Y-m-d H:i:s');
    $pre->bindValue(':email', $input['email'], PDO::PARAM_STR);
    $pre->bindValue(':purchaser_name', $input['purchaser_name'], PDO::PARAM_STR);
    $pre->bindValue(':quantity', (int)$input['quantity'], PDO::PARAM_INT);
    $pre->bindValue(':token', $token, PDO::PARAM_STR);
    $pre->bindValue(':created_at', $now, PDO::PARAM_STR);
    $pre->bindValue(':updated_at', $now, PDO::PARAM_STR);
    // 実行
    $pre->execute();
    // 次の処理のために、購入IDを取得しておく
    $purchase_id = (int)$dbh->lastInsertId();

    // mailの送信
    $send_at = (new DateTimeImmutable())->format('Y-m-d H:i:s');
    $base_url = 'http://game.m-fr.net:8002/';
    $subject = '【チケット購入完了】チケット購入ありがとうございます';
    $body = $twig->render('ticket_purchase_complete.twig', [
        'purchaser_name' => $input['purchaser_name'],
        'quantity' => $input['quantity'],
        'base_url' => $base_url,
        'token' => $token,
    ]);
    // XXX 本当はここでmail送信をする

    // XXX 今回は実際のmail送信は書かないので「mailを送った履歴」DBへのinsertのみ
    // [TODO] email_send_logs テーブルに登録する
    $sql = 'INSERT INTO email_send_logs (ticket_purchase_id, email, purchaser_name, quantity, subject, body, sent_at, created_at, updated_at)
     VALUES (:ticket_purchase_id, :email, :purchaser_name, :quantity, :subject, :body, :sent_at, :created_at, :updated_at)';
    $pre = $dbh->prepare($sql);

    $now = (new DateTimeImmutable())->format('Y-m-d H:i:s');
    $pre->bindValue(':ticket_purchase_id', $purchase_id, PDO::PARAM_INT);
    $pre->bindValue(':email', $input['email'], PDO::PARAM_STR);
    $pre->bindValue(':purchaser_name', $input['purchaser_name'], PDO::PARAM_STR);
    $pre->bindValue(':quantity', (int)$input['quantity'], PDO::PARAM_INT);
    $pre->bindValue(':subject', $subject, PDO::PARAM_STR);
    $pre->bindValue(':body', $body, PDO::PARAM_STR);
    $pre->bindValue(':sent_at', $send_at, PDO::PARAM_STR);
    $pre->bindValue(':created_at', $now, PDO::PARAM_STR);
    $pre->bindValue(':updated_at', $now, PDO::PARAM_STR);
    $pre->execute();

} catch (Exception $e) {
    // XXX 暫定: 本来はlogに出力する & エラーページを出力する
    echo $e->getMessage();
    exit;
}

// 完了ページへのlocation
header('Location: fin_print.php');
