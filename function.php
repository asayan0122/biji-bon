<?php
ini_set('log_errors', 'on');
ini_set('error_log', 'php.log');

session_save_path("/var/tmp/");
ini_set('session.gc_maxlifetime', 60*60*24*30);
ini_set('session.cookie_lifetime', 60*60*24*30);
session_start();
session_regenerate_id();

//========================
//定数設定
//========================
//ERR_MSG
define('ERR01', 'エラーが発生しました。しばらく経ってからやり直してください。');
define('ERR02', '入力必須です。');
define('ERR03', 'このアカウント名は既に使われております。');
define('ERR04', '形式が違います。');
define('ERR05', 'このEmailは既に使われております。');
define('ERR06', 'パスワード（再入力）が違います。');
define('ERR07', '半角英数字のみご利用頂けます。');
define('ERR08', '最低文字数以上で入力して下さい。');
define('ERR09', '指定文字数以内で入力して下さい。');
define('ERR10', 'Emailアドレスもしくはパスワードが違います。');
define('ERR11', 'パスワードが違います。');
define('ERR12', '変更前パスワードと同じです。');
define('ERR13', '認証キーが違います。');
define('ERR14', '認証キー有効期限切れです。');
define('ERR15', '正しく選択されていません。');
define('ERR16', '既に登録されているタイトルです。');
define('ERR17', '半角数字のみご利用頂けます。');
define('ERR18', 'ログインしてください。。');
//SUC_MSG
define('SUC01', 'パスワード変更に成功しました。');
define('SUC02', '認証キー送信に成功しました。');
define('SUC03', '登録しました。');

//global err変数
$err_msg = array();

//========================
//dbConnect() DB管理
//========================
require('personal/info.php');


//========================
//関数
//========================
//デバッグ
$debug_flg = true;
function debug($str)
{
    global $debug_flg;
    if (!empty($debug_flg)) {
        error_log('デバッグ：'.$str);
    }
}
//デバッグログスタート
function debugLogStart()
{
    debug('>>>>>>>>>>>>>>>>>>>>>>>画面処理開始');
    debug('セッションID：'.session_id());
    debug('セッション名：'.print_r($_SESSION, true));
    debug('現在日時スタンプ：'.time());
    if (!empty($_SESSION['login_date']) && !empty($_SESSION['login_limit'])) {
        debug('ログイン期限日時タイムスタンプ：'.($_SESSION['login_date'] + $_SESSION['login_limit']));
    }
}
//ぺージトップ
function pageTop($pagename)
{
    debug('------------------------------------');
    debug($pagename);
    debug('------------------------------------');
}
//クエリ関数＿$stmtを返す
function queryPost($dbh, $sql, $data)
{
    //prepareとexecuteで変数組込
    $stmt = $dbh->prepare($sql);
    //空の場合
    if (!$stmt->execute($data)) {
        debug('クエリに失敗');
        debug('失敗したSQL：'.print_r($stmt, true));
        global $err_msg;
        $err_msg['common'] = ERR01;
        return false;
    }
    debug('クエリ成功');
    return $stmt;
}
//サニタイズ関数
function sanitize($str)
{
    return htmlspecialchars($str, ENT_QUOTES);
}
//ログイン確認
function isLogin()
{
    if (!empty($_SESSION['login_date'])) {
        debug('ログイン済みユーザーです。');
        // 現在日時が最終ログイン日時＋有効期限を超えていた場合
        if (($_SESSION['login_date'] + $_SESSION['login_limit']) < time()) {
            debug('ログイン有効期限オーバーです。');

            // セッションを削除（ログアウトする）
            session_destroy();
            return false;
        } else {
            debug('ログイン有効期限以内です。');
            return true;
        }
    } else {
        debug('未ログインユーザーです。');
        return false;
    }
}
//入力フォーム維持
function getFormData($str, $flg = false)
{
    if ($flg) {
        $method = $_GET;
    } else {
        $method = $_POST;
    }
    global $dbFormData;
    global $err_msg;

    // ユーザーデータがある場合
    if (!empty($dbFormData)) {
        //フォームのエラーがある場合
        if (!empty($err_msg[$str])) {
            //POSTにデータがある場合
            if (isset($method[$str])) {
                return sanitize($method[$str]);
            } else {
                //ない場合（基本ありえない）はDBの情報を表示
                return sanitize($dbFormData[$str]);
            }
        } else {
            //POSTにデータがあり、DBの情報と違う場合
            if (isset($method[$str]) && $method[$str] !== $dbFormData[$str]) {
                return sanitize($method[$str]);
            } else {
                return sanitize($dbFormData[$str]);
            }
        }
    } else {
        if (isset($method[$str])) {
            return sanitize($method[$str]);
        }
    }
}
//エラーメッセージ表示
function errMsg($str)
{
    global $err_msg;
    if (!empty($err_msg[$str])) {
        return $err_msg[$str];
    }
}
//エラー時カラー変更
function errMsgColor($str)
{
    global $err_msg;
    if (!empty($err_msg[$str])) {
        return 'err';
    }
}
//ランダムキー
function makeRandKey($length = 8)
{
    static $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJLKMNOPQRSTUVWXYZ0123456789';
    $str = '';
    for ($i = 0; $i < $length; ++$i) {
        $str .= $chars[mt_rand(0, 61)];
    }
    return $str;
}
//リマインダー用メール送信
function sendMail($from, $to, $subject, $comment)
{
    if (!empty($to) && !empty($subject) && !empty($comment)) {
        mb_language("Japanese");
        mb_internal_encoding("UTF-8");
        //メールを送信
        $result = mb_send_mail($to, $subject, $comment, "From: ".$from);
        //送信結果を判定
        if ($result) {
            debug('メールを送信');
        } else {
            debug('【エラー発生】メールの送信に失敗');
        }
    }
}
//ユーザー情報取得
function getUser($u_id)
{
    debug('ユーザー情報を取得');
    try {
        $dbh = dbConnect();
        $sql = 'SELECT * FROM users WHERE id = :user_id AND delete_flg = 0';
        $data = array(':user_id' => $u_id);
        $stmt = queryPost($dbh, $sql, $data);
        if ($stmt) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            return false;
        }
    } catch (Exception $e) {
        error_log('エラー発生:' . $e->getMessage());
    }
}
//カテゴリー選択
function getCategory()
{
    debug('カテゴリー情報を取得');

    try {
        $dbh = dbConnect();
        $sql = 'SELECT * FROM category';
        $data = array();
        $stmt = queryPost($dbh, $sql, $data);
        if ($stmt) {
            return $stmt->fetchAll();
        } else {
            return false;
        }
    } catch (Exception $e) {
        error_log('エラー発生:' . $e->getMessage());
    }
}
//ターゲット選択
function getTarget()
{
    debug('ターゲット情報を取得');

    try {
        $dbh = dbConnect();
        $sql = 'SELECT * FROM target';
        $data = array();
        $stmt = queryPost($dbh, $sql, $data);
        if ($stmt) {
            return $stmt->fetchAll();
        } else {
            return false;
        }
    } catch (Exception $e) {
        error_log('エラー発生:' . $e->getMessage());
    }
}
//商品データ取得
function getProduct($u_id, $p_id)
{
    debug('商品情報を取得');
    debug('ユーザーID：'.$u_id);
    debug('商品ID：'.$p_id);
    try {
        $dbh = dbConnect();
        $sql = 'SELECT * FROM product WHERE user_id = :u_id AND id = :p_id AND delete_flg = 0';
        $data = array(':u_id' => $u_id, ':p_id' => $p_id);
        $stmt = queryPost($dbh, $sql, $data);
        if ($stmt) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            return false;
        }
    } catch (Exception $e) {
        error_log('エラー発生:' . $e->getMessage());
    }
}
//商品取得
function getProductList($currentMinNum = 1, $category, $target, $span = 15)
{
    debug('商品情報を取得します。');
    try {
        $dbh = dbConnect();
        $sql = 'SELECT id FROM product';
        if (!empty($category)) {
            $sql .= ' WHERE category_id = '.$category;
        }
        if (!empty($target)) {
            $sql .= ' WHERE target_id = '.$target;
        }
        $data = array();
        $stmt = queryPost($dbh, $sql, $data);
        $rst['total'] = $stmt->rowCount(); //総レコード数
        $rst['total_page'] = ceil($rst['total']/$span); //総ページ数
    if (!$stmt) {
        return false;
    }
        // ページング用SQL
        $sql = 'SELECT * FROM product' ;
        if (!empty($category)) {
            $sql .= ' WHERE category_id = '.$category;
        }
        if (!empty($target)) {
            $sql .= ' WHERE target_id = '.$target;
        }
        $sql .= ' LIMIT '.$span.' OFFSET '.$currentMinNum;
        $data = array();
        debug('SQL：'.$sql);
        $stmt = queryPost($dbh, $sql, $data);
        if ($stmt) {
            $rst['data'] = $stmt->fetchAll();
            return $rst;
        } else {
            return false;
        }
    } catch (Exception $e) {
        error_log('エラー発生:' . $e->getMessage());
    }
}
//本の詳細取得関数
function getBooksDetail($p_id)
{
    debug('商品情報を取得します。');
    debug('商品ID：'.$p_id);
    //例外処理
    try {
        $dbh = dbConnect();
        // SQL文作成　//FROM:product->p/category->c
        $sql = 'SELECT p.id ,p.pic, p.comment, p.name, p.author, p.user_id, p.url, p.create_date, p.update_date, c.name AS category FROM product AS p JOIN category AS c WHERE p.id = :p_id AND p.delete_flg = 0 AND c.delete_flg = 0';
        debug('$sql：'.print_r($sql, true));
        
        $data = array(':p_id' => $p_id);
        debug('$data'.print_r($data, true));
        
        $stmt = queryPost($dbh, $sql, $data);
        debug('$stmt'.print_r($stmt, true));
        if ($stmt) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            return false;
        }
        debug('$stmt'.print_r($stmt, true));
    } catch (Exception $e) {
        error_log('エラー発生:' . $e->getMessage());
    }
}

//本のターゲット取得関数
function getBooksTarget($p_id)
{
    debug('商品情報を取得します。');
    debug('商品ID：'.$p_id);
    //例外処理
    try {
        $dbh = dbConnect();
        // //FROM:target->t/category->c
        $sql = 'SELECT t.name AS target FROM product AS p JOIN target AS t WHERE p.id = :p_id AND p.delete_flg = 0 AND t.delete_flg = 0';
        debug('$sql：'.print_r($sql, true));
        
        $data = array(':p_id' => $p_id);
        debug('$data'.print_r($data, true));
        
        $stmt = queryPost($dbh, $sql, $data);
        debug('$stmt'.print_r($stmt, true));
        if ($stmt) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            return false;
        }
        debug('$stmt'.print_r($stmt, true));
    } catch (Exception $e) {
        error_log('エラー発生:' . $e->getMessage());
    }
}
//ユーザー毎お気に入り
function isLike($u_id, $p_id)
{
    debug('お気に入り情報の確認');
    debug('ユーザーID：'.$u_id);
    debug('商品ID：'.$p_id);
    try{
        $dbh = dbConnect();
        $sql = 'SELECT * FROM `like` WHERE product_id = :p_id AND user_id = :u_id';
        $data = array(':u_id' => $u_id, ':p_id' => $p_id);
        $stmt = queryPost($dbh, $sql, $data);
        if ($stmt->rowCount()) {
            debug('お気に入りです');
            return true;
        } else {
            debug('特に気に入ってません');
            return false;
        }
    } catch (Exception $e) {
        error_log('エラー発生:' . $e->getMessage());
    }
}
//お気に入り取得
function getMyLike($u_id)
{
    debug('自分のお気に入り情報を取得');
    debug('ユーザーID：'.$u_id);
    //例外処理
    try {
        // DBへ接続
        $dbh = dbConnect();
        // SQL文作成
        $sql = 'SELECT * FROM `like` AS l LEFT JOIN product AS p ON l.product_id = p.id WHERE l.user_id = :u_id';
        $data = array(':u_id' => $u_id);
        // クエリ実行
        $stmt = queryPost($dbh, $sql, $data);

        if ($stmt) {
            // クエリ結果の全データを返却
            return $stmt->fetchAll();
        } else {
            return false;
        }
    } catch (Exception $e) {
        error_log('エラー発生:' . $e->getMessage());
    }
}

//商品取得
function getMyProducts($u_id)
{
    debug('自分の商品情報');
    debug('ユーザーID：'.$u_id);
    try {
        $dbh = dbConnect();
        $sql = 'SELECT * FROM product WHERE user_id = :u_id AND delete_flg = 0';
        $data = array(':u_id' => $u_id);
        $stmt = queryPost($dbh, $sql, $data);

        if ($stmt) {
            return $stmt->fetchAll();
        } else {
            return false;
        }
    } catch (Exception $e) {
        error_log('エラー発生:' . $e->getMessage());
    }
}
//投稿掲示板
function getMyAccountData($p_id)
{
    debug('アカウント情報');
    try {
        $dbh = dbConnect();
        $sql = 'SELECT product_id, send_date, msg_title, msg, account, icon, age FROM message INNER JOIN users ON user_id = users.id WHERE product_id = :p_id';
        $data = array(':p_id'=>$p_id);
        
        $stmt = queryPost($dbh, $sql, $data);
        debug('$stmt'.print_r($stmt, true));
        if ($stmt) {
            return $stmt->fetchAll();
        } else {
            return false;
        }
    } catch (Exception $e) {
        error_log('エラー発生:' . $e->getMessage());
    }
}
//画像処理
function uploadImg($file, $key)
{
    debug('画像アップロード処理開始');
    debug('FILE情報：'.print_r($file, true));
    //isset:$fileがnullでないか　　　//is_int:$fileの型どうかないか
    if (isset($file['error']) && is_int($file['error'])) {
        try {
            switch ($file['error']) {
          case UPLOAD_ERR_OK:
              break;
          case UPLOAD_ERR_NO_FILE:   // ファイル未選択
              throw new RuntimeException('ファイルが選択されていません');
          case UPLOAD_ERR_INI_SIZE:  // php.ini定義の最大サイズが超過
          case UPLOAD_ERR_FORM_SIZE: // フォーム定義の最大サイズ超過
              throw new RuntimeException('ファイルサイズが大きすぎます');
          default: // その他の場合
              throw new RuntimeException('その他のエラーが発生しました');
      }

            $type = @exif_imagetype($file['tmp_name']);
            if (!in_array($type, [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) {
                throw new RuntimeException('画像形式が未対応です');
            }

            $path = 'uploads/'.sha1_file($file['tmp_name']).image_type_to_extension($type);
            if (!move_uploaded_file($file['tmp_name'], $path)) {
                throw new RuntimeException('ファイル保存時にエラーが発生しました');
            }
            // 保存したファイルパスのパーミッション（権限）を変更する
            chmod($path, 0644);

            debug('ファイルは正常にアップロードされました');
            debug('ファイルパス：'.$path);
            return $path;
        } catch (RuntimeException $e) {
            debug($e->getMessage());
            global $err_msg;
            $err_msg[$key] = $e->getMessage();
        }
    }
}
//画像表示用関数
function showImg($path)
{
    if (empty($path)) {
        return 'img/sample.jpg';
    } else {
        return $path;
    }
}
//アイコン用関数
function showIcon($path)
{
    if (empty($path)) {
        return 'img/icon1.png';
    } else {
        return $path;
    }
}
//ページング機能
// $currentPageNum : 現在のページ数
// $totalPageNum : 総ページ数
// $link : 検索用GETパラメータリンク
// $pageColNum : ページネーション表示数
function pagination($currentPageNum, $totalPageNum, $link = '', $pageColNum = 5)
{
    // 現在のページが、総ページ数と同じ　かつ　総ページ数が表示項目数以上なら、左にリンク４個出す
    if ($currentPageNum == $totalPageNum && $totalPageNum > $pageColNum) {
        $minPageNum = $currentPageNum - 4;
        $maxPageNum = $currentPageNum;
    // 現在のページが、総ページ数の１ページ前なら、左にリンク３個、右に１個出す
    } elseif ($currentPageNum == ($totalPageNum-1) && $totalPageNum > $pageColNum) {
        $minPageNum = $currentPageNum - 3;
        $maxPageNum = $currentPageNum + 1;
    // 現ページが2の場合は左にリンク１個、右にリンク３個だす。
    } elseif ($currentPageNum == 2 && $totalPageNum > $pageColNum) {
        $minPageNum = $currentPageNum - 1;
        $maxPageNum = $currentPageNum + 3;
    // 現ページが1の場合は左に何も出さない。右に５個出す。
    } elseif ($currentPageNum == 1 && $totalPageNum > $pageColNum) {
        $minPageNum = $currentPageNum;
        $maxPageNum = 5;
    // 総ページ数が表示項目数より少ない場合は、総ページ数をループのMax、ループのMinを１に設定
    } elseif ($totalPageNum < $pageColNum) {
        $minPageNum = 1;
        $maxPageNum = $totalPageNum;
    // それ以外は左に２個出す
    } else {
        $minPageNum = $currentPageNum - 2;
        $maxPageNum = $currentPageNum + 2;
    }

    echo '<div class="pagination">';
    echo '<ul class="pagination-list">';
    if ($currentPageNum != 1) {
        echo '<li class="list-item"><a href="?p=1'.$link.'">&lt;</a></li>';
    }
    for ($i = $minPageNum; $i <= $maxPageNum; $i++) {
        echo '<li class="list-item ';
        if ($currentPageNum == $i) {
            echo 'active';
        }
        echo '"><a href="?p='.$i.$link.'">'.$i.'</a></li>';
    }
    if ($currentPageNum != $maxPageNum && $maxPageNum > 1) {
        echo '<li class="list-item"><a href="?p='.$maxPageNum.$link.'">&gt;</a></li>';
    }
    echo '</ul>';
    echo '</div>';
}
//GETパラメータ付与
// $del_key : 付与から取り除きたいGETパラメータのキー
function appendGetParam($arr_del_key = array())
{
    if (!empty($_GET)) {
        $str = '?';
        foreach ($_GET as $key => $val) {
            if (!in_array($key, $arr_del_key, true)) {
                $str .= $key.'='.$val.'&';
            }
        }
        $str = mb_substr($str, 0, -1, "UTF-8");
        return $str;
    }
}
//=========================
//バリデーションチェック
//=========================
//未入力
function validRequired($str, $key)
{
    if ($str === '') {
        global $err_msg;
        $err_msg[$key] = ERR02;
    }
}
//アカウント重複
function validAccountDup($account, $key1, $key2)
{
    global $err_msg;
    try {
        $dbh = dbConnect();
        $sql = 'SELECT count(*) FROM users WHERE account = :account AND delete_flg = 0';
        $data = array(':account' => $account);
        $stmt = queryPost($dbh, $sql, $data);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!empty(array_shift($result))) {
            $err_msg[$key1] = ERR03;
        }
    } catch (Exception $e) {
        error_log('エラー発生:' . $e->getMessage());
        $err_msg[$key2] = ERR01;
    }
}
//Email形式
function validEmail($str, $key)
{
    if (!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $str)) {
        global $err_msg;
        $err_msg[$key] = ERR04;
    }
}
//Email重複
function validEmailDup($email, $key1, $key2)
{
    global $err_msg;
    try {
        $dbh = dbConnect();
        $sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flg = 0';
        $data = array(':email' => $email);

        $stmt = queryPost($dbh, $sql, $data);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!empty(array_shift($result))) {
            $err_msg[$key1] = ERR05;
        }
    } catch (Exception $e) {
        error_log('エラー発生:' . $e->getMessage());
        $err_msg[$key2] = ERR01;
    }
}
//最小文字数
function validMinLen($str, $key, $min = 1)
{
    if (mb_strlen($str) < $min) {
        global $err_msg;
        $err_msg[$key] = ERR08;
    }
}
//最大文字数
function validMaxLen($str, $key, $max = 3000)
{
    if (mb_strlen($str) > $max) {
        global $err_msg;
        $err_msg[$key] = ERR09;
    }
}
//半角
function validHalf($str, $key)
{
    if (!preg_match("/^[a-zA-Z0-9]+$/", $str)) {
        global $err_msg;
        $err_msg[$key] = ERR07;
    }
}
//半角数字(年齢)
function validNumber($str, $key)
{
    if (!preg_match("/^[0-9]+$/", $str)) {
        global $err_msg;
        $err_msg[$key] = ERR17;
    }
}
//パスワード
function validPassCheck($str, $key)
{
    validMinLen($str, $key);
    validMaxLen($str, $key);
    validHalf($str, $key);
}
//パスワード再入力
function validMatch($str1, $str2, $key)
{
    if ($str1 !== $str2) {
        global $err_msg;
        $err_msg[$key] = ERR06;
    }
}
//URL重複
function validUrl($str, $key)
{
    if (!preg_match('/^(https?|ftp)(:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)$/', $str)) {
        global $err_msg;
        $err_msg[$key] = ERR04;
    }
}
//本ののタイトル重複
function validBookTitleDup($name, $key1)
{
    global $err_msg;
    try {
        $dbh = dbConnect();
        $sql = 'SELECT count(*) FROM product WHERE name = :name　AND delete_flg = 0';
        $data = array(':name' => $name);
        $stmt = queryPost($dbh, $sql, $data);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!empty(array_shift($result))) {
            $err_msg[$key1] = ERR16;
        }
    } catch (Exception $e) {
        error_log('エラー発生:' . $e->getMessage());
        $err_msg[$key1] = ERR01;
    }
}