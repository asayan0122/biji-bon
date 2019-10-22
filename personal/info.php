<?php
  function dbConnect()
  {
      $dsn = 'mysql:dbname=bijibon;host=localhost;charset=utf8';
      $user = 'root';
      $password = 'root';
      $options = array(
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
  );
      $dbh = new PDO($dsn, $user, $password, $options);
      return $dbh;
  }