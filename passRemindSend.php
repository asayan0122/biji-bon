<?php
require('function.php');
pageTop('パスワードリマインド送信ページ');
debugLogStart();

if (!empty($_POST)) {
    debug('POST送信');
    debug('POST情報：'.print_r($_POST, true));
    //変数：email
    $email = $_POST['email'];
    //バリデーションチェック
    validRequired($email, 'email');

    if (empty($err_msg)) {
        debug('入力OK');
    
        validEmail($email, 'email');
        validMaxLen($email, 'email');

        if (empty($err_msg)) {
            debug('VCOK。');

            try {
                $dbh = dbConnect();
                $sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flg = 0';
                $data = array(':email' => $email);
                $stmt = queryPost($dbh, $sql, $data);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($stmt && array_shift($result)) {
                    debug('DB登録確認');
                    $auth_key = makeRandKey();
                    $from = 'email';
                    $to = $email;
                    $subject = 'パスワード再発行認証';
                    $comment = <<<EOT
記入のメールアドレス宛にパスワード再発行のご依頼がありました。
下記のURLにて認証キーをご入力頂くとパスワードが再発行されます。

パスワード再発行認証キー入力ページ：http://localhost:8888/biji-bon/passRemindRecieve.php
認証キー：{$auth_key}
※認証キーの有効期限は60分となります

認証キーを再度発行されたい場合は下記ページより再度再発行をお願い致します。
http://localhost:8888/biji-bon/passRemindSend.php

////////////////////////////////////////
BIJI-BON 管理センター
E-mail bijibon.2019@gmail.com                     
////////////////////////////////////////
EOT;
                    sendMail($from, $to, $subject, $comment);
          
                    $_SESSION['auth_key'] = $auth_key;
                    $_SESSION['auth_email'] = $email;
                    $_SESSION['auth_key_limit'] = time()+(60*60);
                    debug('セッション変数の中身：'.print_r($_SESSION, true));
          
                    header("Location:passRemindRecieve.php");
                } else {
                    debug('クエリ失敗');
                    $err_msg['common'] = ERR01;
                }
            } catch (Exception $e) {
                error_log('エラー発生:' . $e->getMessage());
                $err_msg['common'] = ERR01;
            }
        }
    }
}
?>
<?php
$siteTitle = 'パスワード再発行';
require('head.php');
?>

<body class="page-passremindsend page-1colum">

  <!-- メニュー -->
  <?php
    require('header.php');
    ?>

  <!-- メインコンテンツ -->
  <div id="contents" class="site-width">

    <!-- Main -->
    <section id="main">

      <div class="form-container">

        <form action="" method="post" class="form">
          <p>登録のEメールへパスワード再発行用のURLと認証キーを送信します。</p>
          <div class="area-msg">
            <?php echo errMsg('common')?>
          </div>
          <label class="<?php echo errMsgColor('email')?>">
            Email
            <input type="text" name="email" value="<?php echo getFormData('email'); ?>">
          </label>
          <div class="area-msg"><?php echo errMsg('auth_key')?>
          </div>
          <div class="btn-container">
            <input type="submit" class="btn btn-mid" value="送信する">
          </div>
        </form>
      </div>
      <a class="trans-anker" href="login.php">&lt; ログインページへ</a>
    </section>

  </div>

  <!-- footer -->
  <?php
    require('footer.php');
    ?>