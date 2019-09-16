<?php
require('function.php');

pageTop('お気に入りページ');
debugLogStart();

isLogin();

$u_id = $_SESSION['user_id'];

$likeData = getMyLike($u_id);

debug('取得したお気に入りデータ：'.print_r($likeData, true));
debug('画面表示処理終了<<<<<<<<<<<<<<<<<<<<<<<');
?>
<!--HTML-->
<?php
$siteTitle = 'お気に入り一覧';
require('head.php');
?>

<body class="page-likepage page-2colum page-logined">

  <!-- メニュー -->
  <?php
      require('header.php');
    ?>
  <!-- メインコンテンツ -->
  <div id="contents" class="site-width">
    <section id="main">
      <section class="list panel-list">
        <h2 style="text-align:center; margin-bottom:30px;">お気に入り一覧</h2>
        <?php
             if (!empty($likeData)):
              foreach ($likeData as $key => $val):
            ?>
        <a href="productDetail.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&p_id='.$val['id'] : '?p_id='.$val['id']; ?>"
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