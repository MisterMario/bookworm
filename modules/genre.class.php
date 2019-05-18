<?php

class Genre {
  // Изначально методы add и edit принимали информацию о блоке по частям, т.е. $title, $content и т.д.
  // Но для того, чтобы придерживаться некоторых общих правил написания классов я сделал в качестве аргумента массив.
  public static function add($genre) {
    $db = DB::getInstance();
    return $db->query("INSERT INTO ".DB_TABLES["genre"]."(name) VALUES('".$genre["name"]."')");
  }
  public static function edit($genre) {
    $db = DB::getInstance();
    return $db->query("UPDATE ".DB_TABLES["genre"].
                             " SET name='".$genre["name"]."'".
                             " WHERE id='".$genre["id"]."' LIMIT 1");
  }
  public static function removeById($id) {
    $db = DB::getInstance();
    return $db->query("DELETE FROM ".DB_TABLES["genre"]." WHERE id='".$id."' LIMIT 1");
  }
  public static function clean() { // Полностью чистит таблицу. Функция для администратора
    $db = DB::getInstance();
    return $db->query("DELETE FROM ".DB_TABLES["genre"]);
  }
}

?>
