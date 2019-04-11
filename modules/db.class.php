<?php

class DB {
  private static $connect;

  public static function getInstance() {
    if (empty(self::$connect)) {
      self::$connect = new mysqli(DB_SETTINGS["host"], DB_SETTINGS["user"], DB_SETTINGS["pass"], DB_SETTINGS["name"]);
      self::$connect->set_charset("utf8"); // Если между utf и 8 будет "-" - результат будет некорректным.
      if (self::$connect->connect_errno) die("Ошибка соединения с БД!");
    }
    return self::$connect;
  }
  public function __construct() { }
  public function __clone() { }
  public function __wakeup() { }

  public static function checkDBResult($mysql_result) { // Предназначен только для выборок (SELECT FROM)
    if (gettype($mysql_result) == "boolean" || $mysql_result->num_rows == 0)
      return false;
    return true;
  }
}

?>
