<?php
require('function.php');

pageTop('アカウント編集');
debugLogStart();

isLogin();

$dbFormData = getUser($_SESSION['user_id']);

debug('取得したユーザー情報：'.print_r($dbFormData, true));

if (!empty($_POST)) {
    debug('POST送信');
    debug('POST情報：'.print_r($_POST, true));
    debug('FILE情報：'.print_r($_FILES, true));

    //画像をアップロードし、パスを格納
    $icon = (!empty($_FILES['icon']['name'])) ? uploadImg($_FILES['icon'], 'icon') : '';
    $icon = (empty($icon) && !empty($dbFormData['icon'])) ? $dbFormData['icon'] : $icon;
    $account = $_POST['account'];
    $email = $_POST['email'];
    $age = $_POST['age'];

    validRequired($account, 'account');
    validRequired($email, 'email');
    validRequired($age, 'age');
    
    if (empty($err_msg)) {
        if ($dbFormData['account'] !== $account) {
            validMaxLen($account, 'account');
            if (empty($err_msg['account'])) {
                validAccountDup($account, 'account', 'common');
            }
        }
        if ($dbFormData['email'] !== $email) {
            validMaxLen($email, 'email');
            if (empty($err_msg['email'])) {
                validEmailDup($email, 'email', 'common');
            }
            validEmail($email, 'email');
        }
        if ($dbFormData['age'] !== $age) {
            validNumber($age, 'age');
        }
        if (empty($err_msg)) {
            debug('VCOK');

            try {
                $dbh = dbConnect();
                $sql = 'UPDATE users SET account = :account, email = :email, icon = :icon, age = :age WHERE id = :u_id';
                $data = array(':account' => $account , ':email' => $email, ':icon' => $icon, ':age' => $age, ':u_id' => $dbFormData['id']);
                $stmt = queryPost($dbh, $sql, $data);
                if ($stmt) {
                    $_SESSION['msg_success'] = SUC03;
                    debug('インデックスへ遷移します。');
                    header("Location:index.php");
                }
            } catch (Exception $e) {
                error_log('エラー発生:' . $e->getMessage());
                $err_msg['common'] = ERR01;
            }
        }
    }
}
debug('画面表示処理終了<<<<<<<<<<<<<<<<<<<<<<<');
?>
<!--HTML-->
<?php
$siteTitle = 'プロフィール編集';
require('head.php');
?>

<body class="page-profEdit page-2colum page-logined">

  <!-- メニュー -->
  <?php
  require('header.php');
  ?>

  <!-- メインコンテンツ -->
  <div id="contents" class="site-width">
    <h1 class="page-title">プロフィール編集</h1>
    <!-- Main -->
    <section id="main">
      <div class="form-container">
        <form action="" method="post" class="form" enctype="multipart/form-data">
          <div class="area-msg">
            <?php
            if (!empty($err_msg['common'])) {
                echo $err_msg['common'];
            }
            ?>
          </div>
          <!--icon-->
          アイコン
          <label id="area-drop" class="area-drop-icon <?php if (!empty($err_msg['icon'])) {
                echo 'err';
            } ?>">
            <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
            <input type="file" name="icon" id="input-file" class="input-file-icon">
            <img src="<?php echo getFormData('icon'); ?>" alt="" id="prev-img" class="prev-img-icon" style="<?php if (empty(getFormData('icon'))) {
                echo 'display:none;';
            } ?>">クリック
          </label>
          <div class="area-msg">
            <?php echo errMsg('icon')?>
          </div>

          <!--account-->
          <label class="<?php echo errMsgColor('account')?>">
            アカウント名
            <input type="text" name="account" value="<?php echo getFormData('account')?>">
          </label>
          <div class="area-msg">
            <?php echo errMsg('account')?>
          </div>

          <!--Email-->
          <label class="<?php echo errMsgColor('email')?>">
            Email
            <input type="text" name="email" value="<?php echo getFormData('email')?>">
          </label>
          <div class="area-msg">
            <?php echo errMsg('email')?>
          </div>

          <!--age-->
          <label class="<?php echo errMsgColor('age')?>">
            年齢
            <input type="number" name="age" value="<?php echo getFormData('age')?>">
          </label>
          <div class="area-msg">
            <?php echo errMsg('age')?>
          </div>

          <!--変更ボタン-->
          <div class="btn-container">
            <input type="submit" class="btn btn-mid" value="変更する">
          </div>
        </form>
      </div>
    </section>

  </div>

  <!-- footer -->
  <?php
  require('footer.php');
  ?>