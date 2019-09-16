<?php
require('function.php');

pageTop('退会ページ');
debugLogStart();

isLogin();

if (!empty($_POST)) {
    debug('POST送信確認しました。');

    try {
        $dbh = dbConnect();
        $sql = 'UPDATE users SET delete_flg = 1 WHERE id = :us_id';
        $data = array(':us_id' => $_SESSION['user_id']);
        $stmt = queryPost($dbh, $sql, $data);

        if ($stmt) {
            session_destroy();
            debug('セッション変数の中身：'.print_r($_SESSION, true));
            debug('トップページへ遷移します。');
            header("Location:top.php");
        } else {
            debug('送信失敗しました。');
            $err_msg['common'] = ERR01;
        }
    } catch (Exception $e) {
        error_log('エラー発生:' . $e->getMessage());
        $err_msg['common'] = ERR01;
    }
}
debug('画面表示処理終了<<<<<<<<<<<<<<<<<<<<<<<');
?>
<!--HTML-->
<?php
$siteTitle = '退会ページ';
require('head.php');
?>

<body class="page-withdraw page-1colum">

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
          <h2 class="withdraw-title">メンバー退会</h2>
          <div class="area-msg">
            <?php echo errMsg('common');?>
          </div>
          <!--submit-->
          <div class="btn-container">
            <input name="submit" type="submit" class="btn btn-mid" value="退会する">
          </div>
        </form>
      </div>
    </section>

  </div>

  <!--footer-->
  <?php
require("footer.php");
?>