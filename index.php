<?php
require('function.php');
pageTop('メインページ');
debugLogStart();

// GETパラメータを取得
//p　=　GET page数
$currentPageNum = (!empty($_GET['p'])) ? $_GET['p'] : 1;
$category = (!empty($_GET['c_id'])) ? $_GET['c_id'] : '';
$target = (!empty($_GET['t_id'])) ? $_GET['t_id'] : '';

// 表示件数
$listSpan = 15;
// 現在の表示レコード先頭を算出
$currentMinNum = (($currentPageNum-1)*$listSpan);
// DBから本データ取得
$dbProductData = getProductList($currentMinNum, $category, $target);
// DBからカテゴリデータを取得
$dbCategoryData = getCategory();
// DBから対象者データを取得
$dbTargetData = getTarget();

debug('画面表示処理終了<<<<<<<<<<<<<<<<<<<<<<<');
?>
<!--HTML-->
<?php
$siteTitle = 'メインページ';
require('head.php');
?>

<body class="page-top page-2colum">

  <!-- ヘッダー -->
  <?php
      require('header.php');
    ?>

  <!-- メインコンテンツ -->
  <div id="contents" class="site-width">

    <!--検索-->
    <section id="searchbar">
      <form name="" method="get">
        <!--カテゴリ検索-->
        <div class="search1">
          <h1 class="title">カテゴリ<i class="fas fa-book"></i>
          </h1>
          <div class="selectbox"><span class="icn_select"></span>
            <select name="c_id" id="">
              <option value="0" <?php if (getFormData('c_id', true) == 0) {
        echo 'selected';
    } ?>>選択してください</option> <?php
                foreach ($dbCategoryData as $key => $val) {
                    ?> <option value="<?php echo $val['id'] ?>" <?php if (getFormData('c_id', true) == $val['id']) {
                        echo 'selected';
                    } ?>>
                <?php echo $val['name']; ?>
              </option>
              <?php
                }
              ?>
            </select>
          </div>
        </div>

        <!--対象読者検索-->
        <div class="search1">
          <h1 class="title">対象者<i class="fas fa-book-reader"></i>
          </h1>
          <div class="selectbox"><span class="icn_select"></span>
            <select name="t_id" id="">
              <option value="0" <?php if (getFormData('t_id', true) == 0) {
                  echo 'selected';
              } ?>>選択してください</option> <?php
                foreach ($dbTargetData as $key => $val) {
                    ?> <option value="<?php echo $val['id'] ?>" <?php if (getFormData('t_id', true) == $val['id']) {
                        echo 'selected';
                    } ?>>
                <?php echo $val['name']; ?>
              </option>
              <?php
                }
              ?>
            </select>
          </div>
        </div>

        <!--検索-->
        <div class="search2" style="margin: 2px 0;
    padding: 5px 15px;"><input type="submit" value="検索"></div>

      </form>
    </section>

    <!-- Main -->
    <section id="main">
      <!--検索件数-->
      <div class="search-title">
        <div class="search-left">
          <span class="num"><?php echo (!empty($dbProductData['data'])) ? $currentMinNum+1 : 0; ?></span>冊目から<span
            class="num"><?php echo $currentMinNum+count($dbProductData['data']); ?></span>冊目を表示
        </div>

        <div class="search-right">
          全部で<span class="total-num"><?php echo sanitize($dbProductData['total']); ?></span>冊見つかりました
        </div>
      </div>

      <!--リスト-->
      <div class="panel-list">
        <?php
            foreach ($dbProductData['data'] as $key => $val):
          ?>
        <a href="productDetail.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&p_id='.$val['id'] : '?p_id='.$val['id']; ?>"
          class="panel">
          <!--本の画像-->
          <img src="<?php echo sanitize($val['pic']); ?>" alt="<?php echo sanitize($val['name']); ?>">
          <!--タイトル-->
          <p class="panel-title"><?php echo sanitize($val['name']); ?></p>
          <!--著者-->
          <p class="panel-sub">著者：<?php echo sanitize($val['author']); ?></p>
        </a>
        <?php
            endforeach;
          ?> </div>
      <?php pagination($currentPageNum, $dbProductData['total_page']); ?>
    </section>
  </div>

  <!-- footer -->
  <?php
      require('footer.php');
    ?>