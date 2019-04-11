<?php

class Book {
  public static function add($book) { // Принимает массив с информацией о книге
    $db = DB::getInstance();
    return $db->query("INSERT INTO ".DB_TABLES["book"]."(name, author, genre_id, language, tags, series, ".
                      "rightholder, date_of_cos, age_restrictions, isbn, annotation, price, count)".
                      "VALUES('".$book["name"]."', ".
                             "'".$book["author"]."', ".
                             "'".$book["genre_id"]."', ".
                             "'".$book["language"]."', ".
                             "'".$book["keywords"]."', ".
                             "'".$book["series"]."', ".
                             "'".$book["rightholder"]."', ".
                             "'".date("Y-m-d")."', ". // Надо что-то решить с датой начала продаж
                             "'".$book["age_restrictions"]."', ".
                             "'".$book["isbn"]."', ".
                             "'".$book["annotation"]."', ".
                             "'".$book["price"]."', ".
                             "'".$book["count"]."')");
  }
  public static function edit($book) {
    $db = DB::getInstance();
    return $db->query("UPDATE ".DB_TABLES["book"].
                             " SET name='".$book["name"]."', ".
                                  "author='".$book["author"]."', ".
                                  "genre_id='".$book["genre_id"]."', ".
                                  "language='".$book["language"]."', ".
                                  "tags='".$book["keywords"]."', ".
                                  "series='".$book["series"]."', ".
                                  "rightholder='".$book["rightholder"]."', ".
                                  //"date_of_cos='".$book["date_of_cos"]."', ". - думаю этр поле можно не обновлять
                                  "age_restrictions='".$book["age_restrictions"]."', ".
                                  "isbn='".$book["isbn"]."', ".
                                  "annotation='".$book["annotation"]."', ".
                                  "price='".$book["price"]."', ".
                                  "count='".$book["count"]."'".
                              " WHERE id=".$book["id"]." LIMIT 1");
  }
  public static function removeById($id) {
    $db = DB::getInstance();
    return $db->query("DELETE FROM ".DB_TABLES["book"]." WHERE id='".$id."' LIMIT 1");
  }
  public static function clean() { // Полностью чистит таблицу. Функция для администратора
    $db = DB::getInstance();
    return $db->query("DELETE FROM ".DB_TABLES["book"]);
  }
}

?>
