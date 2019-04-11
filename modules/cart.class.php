<?php

// Пространство имен пришлось создать для того, чтобы класс PageBuilder\Cart и Cart не пересекались.
namespace CartControl;

use DB;

class Cart {
  public static function add($user_id, $book_id, $count) {
    $db = DB::getInstance();

    $row_info = $db->query("SELECT id, count FROM ".DB_TABLES["book_ic"]." WHERE user_id=".$user_id." AND book_id=".$book_id." LIMIT 1");
    if (gettype($row_info) != "boolean" && $row_info->num_rows != 0) {

      $row_info = $row_info->fetch_assoc();
      $count = (int)$row_info["count"] + $count;

      return $db->query("UPDATE ".DB_TABLES["book_ic"].
                               " SET count='".$count."' WHERE id=".$row_info["id"]." AND user_id=".$user_id." LIMIT 1");
    }

    return $db->query("INSERT INTO ".DB_TABLES["book_ic"]."(user_id, book_id, count)
                              VALUES('".$user_id."', '".$book_id."', '".$count."')");
  }

  // Не всегда обновляет информацию о книге. Нередко просто добавляет новую запись.
  public static function addBooks($user_id, $books_list) { // Можно будет убрать первый метод. Оставив универсальный.
    $db = DB::getInstance();
    $was_book_added = false;
    $no_errors = true;

    $user_exists = $db->query("SELECT id FROM ".DB_TABLES["user"]." WHERE id='".$user_id."' LIMIT 1");
    if (!DB::checkDBResult($user_exists)) return false;

    while($book = current($books_list)) {
      $book_exists = $db->query("SELECT id FROM ".DB_TABLES["book"]." WHERE id='".$book["id"]."' LIMIT 1");
      if (!DB::checkDBResult($book_exists)) { $no_errors = false; continue; }

      $book_ic_exists = $db->query("SELECT id FROM ".DB_TABLES["book_ic"]." ".
                                   "WHERE book_id='".$book["id"]."' AND user_id='".$user_id."' LIMIT 1");
      if (!DB::checkDBResult($book_ic_exists)) {
        $was_book_added = $db->query("INSERT INTO ".DB_TABLES["book_ic"]."(user_id, book_id, count) ".
                                     "VALUES('".$user_id."', '".$book["id"]."', '".$book["count"]."')");
      } else {
        $was_book_added = $db->query("UPDATE ".DB_TABLES["book_ic"]." As b SET ".
                                                                    "b.count=b.count+".$book["count"]." ".
                                                                    "WHERE b.user_id='".$user_id."' AND ".
                                                                    "b.book_id='".$book["id"]."' LIMIT 1");
      }
      if (!$was_book_added) $no_errors = false;
      next($books_list);
    }

    return $no_errors;
  }

  public static function edit($id, $user_id, $count) { // user_id перется на всякий случай. Вдруг пользователь захочет изменить чужую корзину товаров
    $db = DB::getInstance();
    return $db->query("UPDATE ".DB_TABLES["book_ic"].
                             " SET count='".$count."' WHERE id=".$id." AND user_id=".$user_id." LIMIT 1");
  }

  public static function removeById($id, $user_id) {
    $db = DB::getInstance();
    return $db->query("DELETE FROM ".DB_TABLES["book_ic"]." WHERE id=".$id." AND user_id=".$user_id." LIMIT 1");
  }

  public static function removeByBookId($book_id) {
    $db = DB::getInstance();
    return $db->query("DELETE FROM ".DB_TABLES["book_ic"]." WHERE id='".$book_id."'");
  }

  public static function clear($user_id) {
    $db = DB::getInstance();
    return $db->query("DELETE FROM ".DB_TABLES["book_ic"]." WHERE user_id=".$user_id);
  }

  public static function clean() { // Полностью чистит таблицу. Функция для администратора
    $db = DB::getInstance();
    return $db->query("DELETE FROM ".DB_TABLES["book_ic"]);
  }

  public static function getStatusForUser($user_id) {
    $total_sum = 0;
    $num_books = 0;

    $db = DB::getInstance();
    $res = $db->query("SELECT book_id, count FROM ".DB_TABLES["book_ic"]." WHERE user_id=".$user_id);
    if (gettype($res) == "boolean" || $res->num_rows == 0) return null;

    while ($row = $res->fetch_assoc()) {
      $book = $db->query("SELECT price FROM ".DB_TABLES["book"]." WHERE id=".$row["book_id"]." LIMIT 1");
      if (gettype($book) == "boolean" || $book->num_rows == 0) break;
      $book = $book->fetch_assoc();

      $total_sum += (int)$book["price"] * (int)$row["count"];
      $num_books += (int)$row["count"];
    }
    return array("total_sum" => $total_sum, "count" => $num_books);
  }

  public static function getStatusForNoDBUser() {
    $products_list = "";
    $total_sum = 0;
    $num_books = 0;
    $books_arr = null;

    if (isset($_COOKIE["bw-nodb-cart"])) {
      preg_match_all("/[0-9]+:[0-9]+/", $_COOKIE["bw-nodb-cart"], $books_arr);

      $books_arr = $books_arr[0];

      for ($i = 0; $i < count($books_arr); $i++) {

        $book_id = substr($books_arr[$i], 0, strpos($books_arr[$i], ":"));
        $book_count = substr($books_arr[$i], strpos($books_arr[$i], ":") + 1);

        $db = DB::getInstance();
        $book = $db->query("SELECT name, author, price FROM ".DB_TABLES["book"]." WHERE id=".$book_id." LIMIT 1");
        if (gettype($book) == "boolean" || $book->num_rows == 0) continue;
        $book = $book->fetch_assoc();

        $book["total_sum"] = (int)$book["price"] * (int)$book_count;
        $total_sum += $book["total_sum"];
        $num_books += (int)$book_count;
      }
    }

    return array("total_sum" => $total_sum, "count" => $num_books);
  }

  public static function getBooksListForUser($user_id) { // Ответ: массив в формате: ( (book_id, count), ... )
    $db = DB::getInstance();
    $list = array();

    $res = $db->query("SELECT book_id, count FROM ".DB_TABLES["book_ic"]." WHERE user_id='".$user_id."'");
    if (gettype($res) != "boolean" && $res->num_rows != 0) {

      while($book = $res->fetch_assoc())
        $list[] = array("book_id" => $book["book_id"], "count" => $book["count"]);
    }
    return $list;
  }

  public static function getBooksListForNoDBUser() { // Ответ: массив в формате: ( (book_id, count), ... )
    $db = DB::getInstance();
    $list = array();

    if (isset($_COOKIE["bw-nodb-cart"])) {

      preg_match_all("/[0-9]+:[0-9]+/", $_COOKIE["bw-nodb-cart"], $books_arr);
      $books_arr = $books_arr[0];

      for ($i = 0; $i < count($books_arr); $i++) {
        $book_id = substr($books_arr[$i], 0, strpos($books_arr[$i], ":"));
        $book_count = substr($books_arr[$i], strpos($books_arr[$i], ":") + 1);
        $list[] = array("book_id" => $book_id, "count" => $book_count);
      }
    }
    return $list;
  }

  public static function cartIsEmpty($user_id) { // Проверяет пуста ли корзина пользователя
    $db = DB::getInstance();

    $res = $db->query("SELECT id FROM ".DB_TABLES["book_ic"]." WHERE user_id='".$user_id."' LIMIT 1");
    if (gettype($res) != "boolean" && $res->num_rows != 0)
      return false;

    return true;
  }

  public static function noDBCartIsEmpty() {
    if (isset($_COOKIE["bw-nodb-cart"])) {
      preg_match_all("/[0-9]+:[0-9]+/", $_COOKIE["bw-nodb-cart"], $books_arr);
      $books_arr = $books_arr[0];
      if (count($books_arr) != 0) return false;
    }
    return true;
  }

  public static function cleanNoDBCart() {
    if (isset($_COOKIE["bw-nodb-cart"]))
      setcookie("bw-nodb-cart", "", time()-3600, "/");
  }
}

?>
