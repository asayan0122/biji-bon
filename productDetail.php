<?php
require('function.php');
pageTop('詳細ページ');
debugLogStart();

// 本の詳細表示（画面上）
$p_id = (!empty($_GET['p_id'])) ? $_GET['p_id'] : '';
$detailData = getBooksDetail($p_id);
$detailTargetData = getBooksTarget($p_id);
//パラメータに不正
if (empty($detailData)) {
    error_log('エラー発生:指定ページに不正な値が入りました');
}
debug('取得したDBデータ：'.print_r($detailData, true));

if (!empty($_POST['submit'])) {
    debug('POST送信があります。');
    isLogin();
    header("Location:index.php");
}

//掲示板機能（画面下）
$p_id = (!empty($_GET['p_id'])) ? $_GET['p_id'] : '';
if (!empty($_POST)) {
    debug('POST送信確認');
    $msg_title = $_POST['msg_title'];
    $msg = $_POST['msg'];

    validMaxLen($msg_title, 'msg_title', 30);
    validRequired($msg_title, 'msg_title');
    validMaxLen($msg, 'msg', 200);
    validRequired($msg, 'msg');
    
    if (empty($err_msg)) {
        debug('VCOK');
        //ターゲット選択
        debug('商品ID取得');

        try {
            // DBへ接続
            $dbh = dbConnect();
            // SQL文作成
            $sql = 'INSERT INTO message(product_id, send_date, user_id, msg_title, msg) VALUES (:p_id, :send_date, :user_id, :m_title, :msg)';
            $data = array(':p_id' => $p_id, ':send_date' => date('Y-m-d'), ':user_id' => $_SESSION['user_id'],':m_title' => $msg_title, ':msg' => $msg);
            debug('$data：'.print_r($data, true));
            // クエリ実行
            $stmt = queryPost($dbh, $sql, $data);
            if ($stmt) {
                $suc_msg = '投稿ありがとうございます！！';
                $_POST = array();
            }
        } catch (Exception $e) {
            error_log('エラー発生:' . $e->getMessage());
            $err_msg['common'] =ERR01;
        }
    }
}

//掲示板部分取得
//FetchAllでデータを吸い上げ、引数でID毎に振り分ける
$getMyAccountData = getMyAccountData($p_id);
debug('$getMyAccountData:'.print_r($getMyAccountData, true));
debug('画面表示処理終了<<<<<<<<<<<<<<<<<<<<<<<');
?>
<!--HTML-->
<?php
$siteTitle = '商品詳細';
require('head.php');
?>

<body class="page-productDetail page-1colum">

  <!-- ヘッダー -->
  <?php
      require('header.php');
    ?>

  <!-- メインコンテンツ -->
  <div id="contents" class="site-width">

    <!--全体枠-->
    <section class="product-detail-container">

      <!--本の画像-->
      <section class="container-l">
        <img src="<?php echo showImg(sanitize($detailData['pic'])); ?>"
          alt="メイン画像：<?php echo sanitize($detailData['name']); ?>">
      </section>

      <section class="container-r">
        <!--タイトル・お気に入りボタン-->
        <h3 class="detail-title"><?php echo sanitize($detailData['name']); ?><i class="fa fa-star icn-like js-click-like <?php if (isLike($_SESSION['user_id'], $detailData['id'])) {
        echo 'active';
    } ?>" aria-hidden="true" data-productid="<?php echo sanitize($detailData['id']); ?>"></i></h3>
        <!--著者-->
        <p class="detail-author">
          <span>著者</span><?php echo sanitize($detailData['author']); ?>
        </p>
        <!--カテゴリー-->
        <p class="detail-category"><span>カテゴリ</span>
          <?php echo sanitize($detailData['category']); ?></p>
        <div id="rateYo">
        </div>
        <!--対象-->
        <p class="detail-target"><span>対象者</span><?php echo sanitize($detailTargetData['target']); ?>
        </p>
        <form action="" method="post">
          <input onClick="window.open('<?php echo sanitize($detailData['url']); ?>')" type="submit" value="この本を購入"
            name="submit" class="buy-btn" style="float:right;width: 150px; background:#eb7878;"> </form>
      </section>
      <section class="container-m">
        <!--書評-->
        <div class="detail-commentbox">
          <h3>書評</h3>
          <p>
            <?php echo sanitize($detailData['comment']); ?>
          </p>
        </div>
      </section>

      <!--コメント欄-->
      <section class="container-b">
        <div class="detail-bord">
          <?php
            if (!empty($getMyAccountData)) {
                foreach ($getMyAccountData as $key => $val) {
                    ?>
          <div class="detail-bord box">
            <div class="detail-bord box-l">
              <!--アイコン表示-->
              <div class="bord-icon">
                <img src="<?php echo sanitize(showIcon($val['icon'])); ?>" alt="" class="bord-icon-img">
              </div>
              <!--アカウント名-->
              <div class="bord-account" style="font-size: 15px;font-weight: bold;">
                <?php echo sanitize($val['account']); ?><span>さん</span>
              </div>
              <!--年齢-->
              <div class="bord-age">
                <?php
                echo(sanitize($val['age'])).'歳'; ?>
              </div>
              <!--投稿年月日-->
              <div class="bord-time">
                <time><?php echo date('Y年m月d日', strtotime($val['send_date'])); ?></time>
              </div>
            </div>

            <!--タイトル-->
            <div class="detail-bord box-r">
              <div class="balloon">
                <p class=" bord-msg-title" style="font-size: 14px;">
                  <span
                    style="background: linear-gradient(transparent 60%, #66ffcc 60%);"><?php echo sanitize($val['msg_title']); ?></span>
                </p>
                <!--内容-->
                <p class=" bord-msg-title" style="  font-size:12px;">
                  <?php echo sanitize($val['msg']); ?>
                </p>
              </div>
            </div>
          </div> <?php
                }
            } else {
                ?> <p style="text-align:center;line-height:20;">あなたが初めての投稿者になりませんか？</p>
          <?php
            }
          ?>
        </div>

        <!--コメント送信欄-->
        <div class="area-send-msg">
          <form action="" method="post">
            <p>あなたの書評</p>
            <!--タイトル-->
            <input type="text" name="msg_title" value="<?php echo getFormData('msg_title'); ?>"
              placeholder="タイトル ＊30字以内">
            <div class="area-msg">
              <?php echo errMsg('msg_title');?>
            </div>
            <!--タイトル-->
            <textarea name="msg" id="js-count" cols=" 30" rows="10" placeholder="あなたが読んだ感想やおすすめ点などを書いてください！" style="
                height:80px; margin-bottom: 5px;"><?php echo getFormData('msg'); ?></textarea>
            <p class="counter-text" style="text-arign:center;"><span id="js-count-view">0</span>/200字</p>
            <div class="area-msg">
              <?php echo errMsg('msg');?>
              <?php if (!empty($suc_msg)): ?>
              <p class="suc_msg"><?php echo $suc_msg; ?></p>
              <?php endif; ?>
            </div>
            <input type="submit" value="送信" class="btn btn-send" style="width: 200px;">
          </form>
      </section>
    </section>
  </div>
  <!-- footer -->
  <?php
    require('footer.php');
    ?>