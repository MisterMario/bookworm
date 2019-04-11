<?php

class InfoBlock {
  // Изначально методы add и edit принимали информацию о блоке по частям, т.е. $title, $content и т.д.
  // Но для того, чтобы придерживаться некоторых общих правил написания классов я сделал в качестве аргумента массив.
  public static function add($i_block) {
    $db = DB::getInstance();
    return $db->query("INSERT INTO ".DB_TABLES["i-block"]."(title, content, access_level)
                              VALUES('".$i_block["title"]."', '".htmlspecialchars($i_block["content"], ENT_QUOTES)."', '".$i_block["access_level"]."')");
  }
  public static function edit($i_block) {
    $db = DB::getInstance();
    return $db->query("UPDATE ".DB_TABLES["i-block"].
                             " SET title='".$i_block["title"]."', ".
                                  "content='".htmlspecialchars($i_block["content"], ENT_QUOTES)."', ".
                                  "access_level='".$i_block["access_level"]."' ".
                             " WHERE id='".$i_block["id"]."' LIMIT 1");
  }
  public static function removeById($id) {
    $db = DB::getInstance();
    return $db->query("DELETE FROM ".DB_TABLES["i-block"]." WHERE id='".$id."' LIMIT 1");
  }
  public static function clean() { // Полностью чистит таблицу. Функция для администратора
    $db = DB::getInstance();
    return $db->query("DELETE FROM ".DB_TABLES["i-block"]);
  }
}

?>
