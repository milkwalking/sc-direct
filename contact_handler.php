<?php
// contact_handler.php

// エラーレポーティングの設定（開発中）
error_reporting(E_ALL);
ini_set('display_errors', 1);

// POSTデータの受信と検証
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// フォームデータの受信
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 入力データの取得と検証
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $company = sanitizeInput($_POST['company'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $subject = sanitizeInput($_POST['subject'] ?? '');
    $message = sanitizeInput($_POST['message'] ?? '');

    // 入力バリデーション
    $errors = [];
    if (empty($name)) $errors[] = "名前は必須です。";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "メールアドレスが無効です。";
    if (empty($subject)) $errors[] = "件名は必須です。";
    if (empty($message)) $errors[] = "メッセージは必須です。";

    // エラーがない場合の処理
    if (empty($errors)) {
        // 管理者への通知メール
        $to = 'admin@yourcompany.com'; // 管理者のメールアドレスに変更
        $adminSubject = '新しい問い合わせがありました';
        $adminMessage = "新規問い合わせ詳細:\n\n";
        $adminMessage .= "名前: $name\n";
        $adminMessage .= "メール: $email\n";
        $adminMessage .= "会社名: $company\n";
        $adminMessage .= "電話番号: $phone\n";
        $adminMessage .= "件名: $subject\n";
        $adminMessage .= "メッセージ: $message\n";

        $adminHeaders = "From: webform@yourcompany.com\r\n";
        $adminHeaders .= "Reply-To: $email\r\n";
        $adminHeaders .= "X-Mailer: PHP/" . phpversion();

        // ユーザーへの自動返信メール
        $userSubject = 'お問い合わせ受付完了';
        $userMessage = "$name 様\n\n";
        $userMessage .= "お問い合わせありがとうございます。\n";
        $userMessage .= "担当者が内容を確認し、追って連絡いたします。\n\n";
        $userMessage .= "お問い合わせ内容:\n";
        $userMessage .= "件名: $subject\n";
        $userMessage .= "メッセージ: $message\n\n";
        $userMessage .= "株式会社サンプル";

        $userHeaders = "From: info@yourcompany.com\r\n";

        // メール送信
        $adminMailSent = mail($to, $adminSubject, $adminMessage, $adminHeaders);
        $userMailSent = mail($email, $userSubject, $userMessage, $userHeaders);

        // データベースに保存する場合（オプション）
        if ($adminMailSent && $userMailSent) {
            // データベース接続とデータ保存のコードをここに追加
            
            // リダイレクトと成功メッセージ
            header("Location: thank-you.html");
            exit();
        } else {
            // メール送信エラー処理
            $errors[] = "メールの送信中にエラーが発生しました。";
        }
    }

    // エラーがある場合の処理
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header("Location: contact.html");
        exit();
    }
}
?>