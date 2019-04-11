<?php

class Review {
  public static function add($review) { // Принимает массив с информацией о книге
    $db = DB::getInstance();
    return $db->query("INSERT INTO ".DB_TABLES["review"]."(book_id, user_id, rating, text)".
                      "VALUES('".$review["book_id"]."', ".
                              "'".$review["user_id"]."', ".
                              "'".$review["rating"]."', ".
                              "'".$review["text"]."')");
  }
  public static function edit($review) {
    $db = DB::getInstance();
    return $db->query("UPDATE ".DB_TABLES["review"].
                              " SET book_id='".$review["book_id"]."', ".
                                   "rating='".$review["rating"]."', ".
                                   "text='".$review["text"]."'".
                              " WHERE id='".$review["id"]."' LIMIT 1");
  }
  public static function removeById($id) {
    $db = DB::getInstance();
    return $db->query("DELETE FROM ".DB_TABLES["review"]." WHERE id='".$id."' LIMIT 1");
  }
  public static function removeByBookId($book_id) {
    $db = DB::getInstance();
    return $db->query("DELETE FROM ".DB_TABLES["review"]." WHERE book_id='".$book_id."'");
  }
  public static function clean() { // Полностью чистит таблицу. Функция для администратора
    $db = DB::getInstance();
    return $db->query("DELETE FROM ".DB_TABLES["review"]);
  }
  public static function getLastId() {
    $db = DB::getInstance();
    $res = $db->query("SELECT id FROM ".DB_TABLES["review"]." ORDER BY id DESC LIMIT 1");
    if (gettype($res) != "boolean" && $res->num_rows != 0)
      return (int)$res->fetch_assoc()["id"];

    return false;
  }
}

?>
