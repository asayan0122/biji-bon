<?php
require('function.php');

pageTop('商品登録・編集ページ');
debugLogStart();

$p_id = (!empty($_GET['p_id'])) ? $_GET['p_id'] : '';
$dbFormData = (!empty($p_id)) ? getProduct($_SESSION['user_id'], $p_id) : '';
// $editかどうかを確認
$edit_flg = (empty($dbFormData)) ? false : true;
$dbCategoryData = getCategory();
debug('商品ID：'.$p_id);
debug('フォーム用DBデータ：'.print_r($dbFormData, true));
debug('カテゴリデータ：'.print_r($dbCategoryData, true));
$dbTargetData = getTarget();

if (!empty($_POST)) {
    debug('POST送信確認');
    debug('POST情報：'.print_r($_POST, true));
    debug('FILE情報：'.print_r($_FILES, true));

    $comment = $_POST['comment'];
    $name = $_POST['name'];
    $author = $_POST['author'];
    $category = $_POST['category_id'];
    $target = $_POST['target_id'];
    $url = $_POST['url'];

    $pic = (!empty($_FILES['pic']['name'])) ? uploadImg($_FILES['pic'], 'pic') : '';
    $pic= (empty($pic) && !empty($dbFormData['pic'])) ? $dbFormData['pic'] : $pic;
    debug('$pic.の中身：'.print_r($pic, true));

    //新規：バリデーション
    if (empty($dbFormData)) {
        validRequired($name, 'name');
        validMaxLen($name, 'name');
        validBookTitleDup($name, 'name');

        validMaxLen($comment, 'comment', 3000);

        validRequired($author, 'author');
        validMaxLen($author, 'author');

        validRequired($category, 'category_id');

        validRequired($target, 'target_id');

        validRequired($url, 'url');
        validMaxLen($url, 'url');
        validUrl($url, 'url');
    } else {
        //編集:バリデーション
        if ($dbFormData['name'] !== $name) {
            validRequired($name, 'name');
            validMaxLen($name, 'name');
        }
        if ($dbFormData['comment'] !== $comment) {
            validMaxLen($comment, 'comment', 3000);
        }
        if ($dbFormData['author'] !== $author) {
            validRequired($author, 'author');
            validMaxLen($author, 'author');
        }
        if ($dbFormData['category_id'] !== $category) {
            validRequired($category, 'category_id');
        }
        if ($dbFormData['target_id'] !== $target) {
            validRequired($target, 'target_id');
        }
        if ($dbFormData['url'] !== $url) {
            validRequired($url, 'url');
            validMaxLen($url, 'url');
            validUrl($url, 'url');
        }
    }

    if (empty($err_msg)) {
        debug('バリデーションOKです。');

        try {
            $dbh = dbConnect();
            debug('$dbhの中身：'.print_r($dbh, true));
            if ($edit_flg) {
                debug('DB更新です。');
                $sql = 'UPDATE product SET pic = :pic, name = :name, comment = :comment, author = :author, category_id = :category, target_id = :target, url = :url WHERE user_id = :u_id AND id = :p_id' ;
                debug('$sql.の中身：'.print_r($sql, true));

                $data = array(':pic' => $pic, ':name' => $name , ':comment' => $comment, ':author' => $author,':category' => $category,':target' => $target, ':url' =>$url, ':u_id' => $_SESSION['user_id'], ':p_id' => $p_id);
                debug('$data.の中身：'.print_r($data, true));
            } else {
                debug('DB新規登録です。');
                $sql = 'INSERT INTO product (pic, comment, name, author, category_id, target_id, url, user_id, create_date ) values (:pic, :comment, :name, :author, :category, :target, :url, :u_id, :date)';
                $data = array(':pic' => $pic,':comment' => $comment,':name' => $name , ':author' => $author,':category' => $category,':target' => $target, ':url' => $url , ':u_id' => $_SESSION['user_id'], ':date' => date('Y-m-d H:i:s'));
            }
            debug('SQL：'.$sql, true);
            debug('$data：'.print_r($data, true));
            
            $stmt = queryPost($dbh, $sql, $data);
  
            if ($stmt) {
                debug('トップページへ遷移');
                header("Location:index.php");
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
$siteTitle = (!$edit_flg) ? '登録' : '編集';
require('head.php');
?>

<body class="page-reistProduct page-2colum page-logined">

  <!-- メニュー -->
  <?php
    require('header.php');
    ?>

  <!-- メインコンテンツ -->
  <div id="contents" class="site-width">
    <h1 class="page-title"><?php echo (!$edit_flg) ? '登録ページ' : '編集ページ'; ?></h1>
    </h1>
    <!-- Main -->
    <section id="product-main">
      <div class="form-container">
        <form action="" method="post" class="form" enctype="multipart/form-data"
          style="width:100%;box-sizing:border-box;">
          <div class="area-msg">
            <?php echo errMsg('common')?>
          </div>
          <div class="contents-wrap">
            <div class="contents-wrap-l">

              <!--本の画像-->
              <div class="imgDrop-container">
                本の画像を登録
                <label id="area-drop" class="area-drop-book">
                  <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                  <input type="file" name="pic" id="input-file" class="input-file-book">
                  <img src="<?php echo getFormData('pic'); ?>" alt="" id="prev-img" class=" prev-img-book" style="<?php if (empty(getFormData('pic'))) {
        echo 'display:none;';
    } ?>">ドラッグ＆ドロップ
                </label>
                <div class="area-msg">
                  <?php echo errMsg('pic')?>
                </div>
              </div>

              <!--タイトル-->
              <label class="<?php echo errMsgColor('name')?>">
                タイトル<span class="label-require">必須</span>
                <input type="text" name="name" value="<?php echo getFormData('name');?>">
              </label>
              <div class="area-msg"> <?php echo errMsg('name')?></div>

              <!--書評-->
              <label class="<?php echo errMsgColor('comment')?>">
                書評
                <textarea name=" comment" id="js-count" cols=" 30" 　 rows="10" placeholder="3000字以内で入力してください" style="
                height:80px; margin-bottom: 5px;"><?php echo getFormData('comment'); ?></textarea>
              </label>
              <p class="counter-text"><span id="js-count-view">0</span>文字</p>
              <div class="area-msg">
                <?php echo errMsg('comment')?>
              </div>
            </div>
            <div class="contents-wrap-r">

              <!--著者-->
              <label class="<?php echo errMsgColor('author')?>">
                著者名<span an class="label-require">必須</span>
                <input type="text" name="author" value="<?php echo getFormData('author'); ?>">
              </label>
              <div class="area-msg"> <?php echo errMsg('author')?>
              </div>

              <!--リンク先-->
              <label class="<?php echo errMsgColor('url')?>">
                URL<span class="label-require">必須</span>
                <input type="text" name="url" value="<?php echo getFormData('url'); ?>">
              </label>
              <div class="area-msg"> <?php echo errMsg('url')?>
              </div>

              <!--カテゴリ-->
              <label class="<?php echo errMsgColor('category_id')?>">
                カテゴリ<span class="label-require">必須</span>
                <select name="category_id" id="">
                  <option value="0" <?php if (getFormData('category_id') == 0) {
        echo 'selected';
    } ?>>
                    選択してください
                    <?php foreach ($dbCategoryData as $key => $val) {
        ?>
                  <option value=" <?php echo $val['id']?>" <?php if (getFormData('category_id') == $val['id']) {
            echo 'selected';
        } ?>>
                    <?php echo $val['name']; ?>
                  </option>
                  <?php
    }
                ?>
                </select>
              </label>
              <div class="area-msg">
                <?php echo errMsg('category_id') ?>
              </div>

              <!--対象-->
              <label class="<?php echo errMsgColor('target_id')?>">
                対象者<span class="label-require">必須</span>
                <select name="target_id" id="">
                  <option value="0" <?php if (getFormData('target_id') == 0) {
                    echo 'selected';
                } ?>>
                    選択してください
                    <?php foreach ($dbTargetData as $key => $val) {
                    ?>
                  <option value=" <?php echo $val['id']?>" <?php if (getFormData('target_id') == $val['id']) {
                        echo 'selected';
                    } ?>>
                    <?php echo $val['name']; ?>
                  </option>
                  <?php
                }
                ?>
                </select>
              </label>
              <div class="area-msg">
                <?php echo errMsg('category_id') ?>
              </div>

              <div class="btn-container">
                <input type="submit" class="btn btn-mid" value="<?php echo (!$edit_flg) ? '登録' : '更新'; ?>">
              </div>
            </div>
        </form>
      </div>
    </section>
  </div>

  <!-- footer -->
  <?php
    require('footer.php');
    ?>