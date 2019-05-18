<?php

namespace PageBuilder;

use DB;


class GenresEditor {
  public static function getEditHTML($id) {
    $db = DB::getInstance();
    $isEdit = true;

    $res = $db->query("SELECT * FROM ".DB_TABLES["genre"]." WHERE id='".$id."' LIMIT 1");
    if (gettype($res) == "boolean" || $res->num_rows == 0) return null;
    $res = $res->fetch_assoc();

    $genre = array("id" => $res["id"], "name" => $res["name"]);

    ob_start(); include SERVER_VIEW_DIR."editor_genre.html";
    return ob_get_clean();
  }

  public static function getNewHTML() {
    $isEdit = false;

    $genre = array("id" => 0, "name" => "");
    ob_start(); include SERVER_VIEW_DIR."editor_genre.html";
    return ob_get_clean();
  }
}

 ?>
