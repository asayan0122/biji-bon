<?php
require('function.php');

pageTop('本を編集');
debugLogStart();

isLogin();

// 画面表示用データ取得
$u_id = $_SESSION['user_id'];
// DBから商品データを取得
$productData = getMyProducts($u_id);

debug('取得した商品データ：'.print_r($productData, true));
debug('画面表示処理終了<<<<<<<<<<<<<<<<<<<<<<<');
?>
<!--HTML-->
<?php
$siteTitle = '登録リスト';
require('head.php');
?>

<body class="page-productEdit page-2colum">


  <!-- メニュー -->
  <?php
      require('header.php');
    ?>
  <!-- メインコンテンツ -->
  <div id="contents" class="site-width">

    <!-- Main -->
    <section id="main">
      <section class="list panel-list">
        <h2 style="text-align:center; margin-bottom:30px;">登録リスト</h2>
        <?php
             if (!empty($productData)):
              foreach ($productData as $key => $val):
            ?>
        <a href="registProduct.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&p_id='.$val['id'] : '?p_id='.$val['id']; ?>"
          class="panel">
          <img src="<?php echo showImg(sanitize($val['pic'])); ?>" alt="<?php echo sanitize($val['name']); ?>">
          <p class="panel-title"><?php echo sanitize($val['name']); ?></p>
          <p class="panel-sub">著者：<?php echo sanitize($val['author']); ?></p>
        </a>
        <?php
              endforeach;
             endif;
            ?>
      </section>
  </div>
  <!-- footer -->
  <?php
      require('footer.php');
    ?>