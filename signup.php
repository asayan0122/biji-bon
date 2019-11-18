<?php
require('function.php');

pageTop('サインアップページ');
debugLogStart();

if (!empty($_POST)) {
    debug('START');
    debug('POST送信');

    $account = $_POST['account'];
    $email = $_POST['email'];
    $pass = $_POST['pass'];
    $pass_re = $_POST['pass_re'];

    validRequired($account, 'account');
    validRequired($email, 'email');
    validRequired($pass, 'pass');
    validRequired($pass_re, 'pass_re');

    if (empty($err_msg)) {
        //account
        validAccountDup($account, 'account', 'common');
        validmaxLen($account, 'account');
        //mail
        validEmailDup($email, 'email', 'common');
        validEmail($email, 'email');
        validMaxLen($email, 'email');
        //pass
        validHalf($pass, 'pass');
        validMaxLen($pass, 'pass');
        validMinLen($pass, 'pass');
        //pass_re
        validMaxLen($pass_re, 'pass_re');
        validMinLen($pass_re, 'pass_re');
  
        if (empty($err_msg)) {
            validMatch($pass, $pass_re, 'pass_re');
            if (empty($err_msg)) {
                debug('VC完了');
                debug('DB接続');

                try {
                    $dbh = dbConnect();
                    $sql = 'INSERT INTO users(account,email,`password`,login_time,create_date) VALUES (:account,:email,:pass,:login_time,:create_date)';
                    $data = array(':account' => $account, ':email' => $email, ':pass' => password_hash($pass, PASSWORD_DEFAULT), ':login_time' =>date('Y-m-d H:i:s') ,':create_date'=>date('Y-m-d H:i:s'));
                    $stmt = queryPost($dbh, $sql, $data);

                    if ($stmt) {
                        $sessionLimit = 60 * 60;
                        $_SESSION['login_date'] = time();
                        $_SESSION['login_limit'] = $sessionLimit;
                        $_SESSION['user_id'] = $dbh->lastInsertId();

                        debug('SESSION'.print_r($_SESSION, true));
                        header("Location:top.php");
                    }
                } catch (Exception $e) {
                    error_log('エラー発生：'.$e->getMessage());
                    $err_msg['common'] = ERR01;
                }
            }
        }
    }
}

?>


<!--HTML-->
<?php
$siteTitle = '新規ユーザー登録';
require('head.php');
?>

<body class="page-signup page-1colum">

  <!-- ヘッダー -->
  <?php
require('header.php');
?>


  <!-- メインコンテンツ-->
  <div id="contents" class="site-width">

    <!-- Main -->
    <section id="main">
      <div class="form-container">
        <form action="" method="post" class="form">
          <h2>新規ユーザー登録</h2>
          <div class="area-msg"><?php echo errMsg('common')?></div>

          <!--account-->
          <label class="<?php echo errMsgColor('account')?>">アカウント名
            <input type="text" name="account" value="<?php echo getFormData('account')?>">
          </label>
          <div class="area-msg"><?php echo errMsg('account')?></div>

          <!--email-->
          <label class="<?php echo errMsgColor('email')?>">Email
            <input type="text" name="email" placeholder="●●●●@▲▲.▲▲" value="<?php echo getFormData('email')?>">
          </label>
          <div class="area-msg"><?php echo errMsg('email')?></div>

          <!--pass-->
          <label class="<?php echo errMsgColor('pass')?>">
            パスワード
            <input type="password" name="pass" placeholder="英数字8文字以上" value="<?php echo getFormData('pass')?>">
          </label>
          <div class="area-msg"><?php echo errMsg('pass')?></div>

          <!--pass_re-->
          <label class="<?php echo errMsgColor('pass_re')?>">パスワード
            <input type="password" name="pass_re" placeholder="再入力" value="<?php echo getFormData('pass_re')?>">
          </label>
          <div class="area-msg"><?php echo errMsg('pass_re')?></div>

          <!--submit-->
          <div class="btn-container">
            <input type="submit" class="btn btn-mid" value="新規登録">
          </div>
        </form>
      </div>
    </section>

  </div>

  <!-- フッター -->
  <?php
require("footer.php");
?>