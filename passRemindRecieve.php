<?php
require('function.php');

pageTop('パスワードリマインド認証入力ページ');
debugLogStart();

if (!empty($_POST)) {
    debug('POST送信');
    debug('POST情報：'.print_r($_POST, true));
    //変数：認証キー
    $auth_key = $_POST['auth_key'];
    //バリデーションチェック
    validRequired($auth_key, 'auth_key');

    if (empty($err_msg)) {
        debug('入力OK');
  
        validLength($auth_key, 'auth_key');
        validHalf($auth_key, 'auth_key');

        if (empty($err_msg)) {
            debug('VCOK');
     
            if ($auth_key !== $_SESSION['auth_key']) {
                $err_msg['common'] = ERR13;
            }
            if (time() > $_SESSION['auth_key_limit']) {
                $err_msg['common'] = ERR14;
            }
     
            if (empty($err_msg)) {
                debug('認証OK');
       
                $pass = makeRandKey();
                //DB接続
                try {
                    $dbh = dbConnect();
                    $sql = 'UPDATE users SET `password` = :pass WHERE email = :email AND delete_flg = 0';
                    $data = array(':email' => $_SESSION['auth_email'], ':pass' => password_hash($pass, PASSWORD_DEFAULT));
                    $stmt = queryPost($dbh, $sql, $data);

                    if ($stmt) {
                        debug('クエリ成功。');

                        $from = 'email';
                        $to = $_SESSION['auth_email'];
                        $subject = '【パスワード再発行完了】';
                        $comment = <<<EOT
記入のメールアドレス宛にパスワードの再発行を致しました。
下記のURLにて再発行パスワードをご入力頂き、ログインください。

ログインページ：http://localhost:8888/biji-bon/login.php
再発行パスワード：{$pass}
※ログイン後、パスワードのご変更をお願い致します

////////////////////////////////////////
BIJI-BON管理センター
E-mail bijibon.2019@gmail.com
////////////////////////////////////////
EOT;
                        sendMail($from, $to, $subject, $comment);

                        session_unset();
                        $_SESSION['msg_success'] = SUC02;
                        debug('セッション変数の中身：'.print_r($_SESSION, true));

                        header("Location:login.php");
                    } else {
                        debug('クエリに失敗しました。');
                        $err_msg['common'] = ERR01;
                    }
                } catch (Exception $e) {
                    error_log('エラー発生:' . $e->getMessage());
                    $err_msg['common'] = ERR01;
                }
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
          <p>ご指定のEmailに記載されている【認証キー】をご入力ください。</p>
          <div class="area-msg">
            <?php echo errMsg('common')?>
          </div>
          <label class="<?php echo errMsgColor('email')?>">
            認証キー
            <input type="text" name="auth_key" value="<?php echo getFormData('auth_key'); ?>">
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