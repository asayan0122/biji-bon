<?php
require('function.php');

pageTop('パスワード変更');
debugLogStart();

isLogin();
//ユーザー情報取得
$userData = getUser($_SESSION['user_id']);
debug('ユーザー：'.print_r($userData, true));

if (!empty($_POST)) {
    debug('POST送信があります。');
    debug('POST情報：'.print_r($_POST, true));
    //変数
    $pass_bef = $_POST['pass_bef'];
    $pass_aft = $_POST['pass_aft'];
    $pass_aft_re = $_POST['pass_aft_re'];
    //バリデーションチェック
    validRequired($pass_bef, 'pass_bef');
    validRequired($pass_aft, 'pass_aft');
    validRequired($pass_aft_re, 'pass_aft_re');

    if (empty($err_msg)) {
        debug('未入力チェックOK');

        validPassCheck($pass_bef, 'pass_bef');
        validPassCheck($pass_aft, 'pass_aft');

        if (!password_verify($pass_bef, $userData['password'])) {
            $err_msg['pass_bef'] = ERR11;
        }
        if ($pass_bef === $pass_aft) {
            $err_msg['pass_aft'] = ERR12;
        }
        validMatch($pass_aft, $pass_aft_re, 'pass_aft_re');

        if (empty($err_msg)) {
            debug('パスワード入力チェックOK');
            //DB接続
            try {
                $dbh = dbConnect();
                $sql = 'UPDATE users SET `password` = :pass WHERE id = :u_id';
                $data = array(':u_id' => $_SESSION['user_id'], ':pass' => password_hash($pass_aft, PASSWORD_DEFAULT));
                $stmt = queryPost($dbh, $sql, $data);

                if ($stmt) {
                    debug('クエリ成功。');
                    $_SESSION['msg_success'] = SUC01;
                    header("Location:index.php");
                } else {
                    debug('クエリに失敗。');
                    $err_msg['common']= ERR01;
                }
            } catch (Exception $e) {
                error_log('エラー発生:' . $e->getMessage());
                $err_msg['common'] = ERR01;
            }
        }
    }
}
?>

<!--HTML-->
<?php
$siteTitle = 'パスワード変更';
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
          <h2>パスワード変更</h2>
          <div class="area-msg"><?php echo errMsg('common')?></div>

          <!--pass_bef-->
          <label class="<?php echo errMsgColor('pass_bef')?>">
            変更前パスワード
            <input type="password" name="pass_bef" value="<?php echo getFormData('pass_bef');?>"></label>
          <div class="area-msg"><?php echo errMsg('pass_bef')?></div>

          <!--pass_aft-->
          <label class="<?php echo errMsgColor('pass_aft')?>">
            変更後パスワード
            <input type="password" name="pass_aft" placeholder="英数字8文字以上" value="<?php echo getFormData('pass_aft');?>">
          </label>
          <div class="area-msg"><?php echo errMsg('pass_aft') ?></div>

          <!--pass_aft_re-->
          <label class="<?php echo errMsgColor('pass_aft_re')?>">変更後パスワード
            <input type="password" name="pass_aft_re" placeholder="再入力"
              value="<?php echo getFormData('pass_aft_re');?>">
          </label>
          <div class="area-msg"><?php echo errMsg('pass_aft_re')?></div>

          <!--submit-->
          <div class="btn-container">
            <input type="submit" class="btn btn-mid" value="登録">
          </div>
        </form>
      </div>
    </section>

  </div>

  <!-- フッター -->
  <?php
require("footer.php");
?>