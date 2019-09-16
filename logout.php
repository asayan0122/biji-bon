<?php
require('function.php');
pageTop('ログアウトページ');
debugLogStart();

debug('ログアウトします。');
session_destroy();

debug('トップページへ遷移します。');
header("Location:top.php");