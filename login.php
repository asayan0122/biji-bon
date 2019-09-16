<?php
require('function.php');

pageTop('ログインページ');
debugLogStart();

if (!empty($_POST)) {
    debug('POST送信');
  
    $email = $_POST['email'];
    $pass = $_POST['pass'];
    $save_log = (!empty($_POST['save_log']))? true : false;
  
    //バリデーションチェック　
    validRequired($email, 'email');
    validRequired($pass, 'pass');

    if (empty($err_msg)) {
        debug('VC完了');
        debug('DB接続');
        //DBへ接続
        try {
            $dbh = dbConnect();
            $sql = 'SELECT `password`,id FROM users WHERE email = :email AND delete_flg = 0';
            $data = array(':email' => $email);
            $stmt = queryPost($dbh, $sql, $data);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            debug('query：'.print_r($result, true));

            if (!empty($result) && password_verify($pass, array_shift($result))) {
                debug('pass確認');
                //セッションリミットを１日
                $sessionLimit = 60*60*24;
                $_SESSION['login_date'] = time();
               
                if ($save_log) {
                    debug('次回ログイン維持');
                    //ログイン維持にチェックがあるときは１ヶ月延長
                    $_SESSION['login_limit'] = $sessionLimit * 30;
                } else {
                    debug('次回ログイン維持無し');
                    $_SESSION['login_limit'] = $sessionLimit;
                }
                $_SESSION['user_id'] = $result['id'];
                debug('SESSION：'.print_r($_SESSION, true));
                header("Location:top.php");
            } else {
                $err_msg['common'] = ERR10;
            }
        } catch (Exception $e) {
            error_log('エラー発生:' . $e->getMessage());
            $err_msg['common'] = ERR01;
        }
    }
}
debug('画面表示処理終了<<<<<<<<<<<<<<<<<<<<<<<');

?>


<!--HTML-->
<?php
$siteTitle = 'ログイン';
require('head.php');
?>

<body class="page-login page-1colum">

  <!-- ヘッダー -->
  <?php
require('header.php');
?>


  <!-- メインコンテンツ -->
  <div id="contents" class="site-width">

    <!-- Main -->
    <section id="main">
      <div class="form-container">
        <form action="" method="post" class="form">
          <h2>ログイン画面</h2>
          <div class="area-msg"><?php echo errMsg('common')?></div>

          <!--email-->
          <label class="<?php echo errMsgColor('email')?>">Email
            <input type="text" name="email" value="<?php echo getFormData('email')?>">
          </label>
          <div class="area-msg"><?php echo errMsg('email')?></div>

          <!--pass-->
          <label class="<?php echo errMsgColor('pass')?>">
            パスワード
            <input type="password" name="pass" value="<?php echo getFormData('pass')?>">
          </label>
          <div class="area-msg"><?php echo errMsg('pass')?></div>

          <!--自動ログイン-->
          <label>
            <input type="checkbox" name="save_log">次回ログイン省略
          </label>

          <!--submit-->
          <div class="btn-container">
            <input type="submit" class="btn btn-mid" value="ログイン">
          </div>

          <div class="passre-icon"><a class="trans-anker" href="passRemindSend.php">パスワード再設定</a></div>
        </form>
      </div>
    </section>

  </div>

  <!-- フッター -->
  <?php
require("footer.php");
?>