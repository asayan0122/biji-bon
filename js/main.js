$(function() {
  $("#rateYo").rateYo({
    rating: 3.8
  });
});
$(function() {
  $(".nav li").hover(
    function() {
      $("ul:not(:animated)", this).slideDown();
    },
    function() {
      $("ul.nav_menu", this).slideUp();
    }
  );
  // フッターを最下部に固定
  var $ftr = $("#footer");
  if (window.innerHeight > $ftr.offset().top + $ftr.outerHeight()) {
    $ftr.attr({
      style:
        "position:fixed; top:" +
        (window.innerHeight - $ftr.outerHeight()) +
        "px;"
    });
  }
  // 画像ライブプレビュー
  var $dropArea = $("#area-drop");
  var $fileInput = $("#input-file");
  $dropArea.on("dragover", function(e) {
    e.stopPropagation();
    e.preventDefault();
    $(this).css("border", "3px #ccc dashed");
  });
  $dropArea.on("dragleave", function(e) {
    e.stopPropagation();
    e.preventDefault();
    $(this).css("border", "none");
  });
  $fileInput.on("change", function(e) {
    $dropArea.css("border", "none");
    var file = this.files[0],
      $img = $(this).siblings("#prev-img"),
      fileReader = new FileReader();
    fileReader.onload = function(event) {
      $img.attr("src", event.target.result).show();
    };
    fileReader.readAsDataURL(file);
  });

  // テキストエリアカウント
  var $countUp = $("#js-count"),
    $countView = $("#js-count-view");
  $countUp.on("keyup", function(e) {
    $countView.html($(this).val().length);
  });

  // お気に入り登録・削除
  var $like, likeProductId;
  $like = $(".js-click-like") || null;
  likeProductId = $like.data("productid") || null;
  if (likeProductId !== undefined && likeProductId !== null) {
    $like.on("click", function() {
      var $this = $(this);
      $.ajax({
        type: "POST",
        url: "ajaxLike.php",
        data: {
          productId: likeProductId
        }
      })
        .done(function(data) {
          console.log("Ajax Success");
          $this.toggleClass("active");
        })
        .fail(function(msg) {
          console.log("Ajax Error");
        });
    });
  }
});
$(window).on("load", function() {
  fitImg($(".js-fit-hero"));
  $(".js-fit-post-img").each(function() {
    fitImg($(this));
  });
});

$(window).on("resize", function() {
  fitImg($(".js-fit-hero"));

  $(".js-fit-post-img").each(function() {
    fitImg($(this));
  });
});

function fitImg(object) {
  //親要素取得
  var parent = object.parent();

  //画像サイズ取得
  var imgW = object.width();
  var imgH = object.height();

  //フィットさせる親要素のサイズ取得
  var parentW = parent.width();
  var parentH = parent.height();

  //幅・高さの拡大率取得
  var scaleW = parentW / imgW;
  var scaleH = parentH / imgH;

  //幅・高さの拡大率の大きいものを取得
  var fixScale = Math.max(scaleW, scaleH);

  //画像の幅高さを設定
  var setW = imgW * fixScale;
  var setH = imgH * fixScale;

  //画像の位置を設定
  var moveX = Math.floor((parentW - setW) / 2);
  var moveY = Math.floor((parentH - setH) / 2);

  //設定した数値でスタイルを適用
  //親要素のスタイル
  parent.css({
    overflow: "hidden",
    position: "relative"
  });
  //フィットさせる要素のスタイル
  object.css({
    position: "absolute",
    width: setW,
    height: setH,
    left: moveX,
    top: moveY
  });
}
