<?php

namespace PageBuilder;

use DB;


class InfoBlocksEditor {
  public static function getEditHTML($id) {
    $db = DB::getInstance();
    $isEdit = true;

    $res = $db->query("SELECT * FROM ".DB_TABLES["i-block"]." WHERE id='".$id."' LIMIT 1");
    if (gettype($res) == "boolean" || $res->num_rows == 0) return null;
    $res = $res->fetch_assoc();

    $i_block = array("id" => $res["id"], "title" => $res["title"], "content" => $res["content"], "access_level" => $res["access_level"]);

    ob_start(); include SERVER_VIEW_DIR."editor_info_block.html";
    return ob_get_clean();
  }

  public static function getNewHTML() {
    $isEdit = false;

    $i_block = array("id" => 0, "title" => "", "content" => "", "access_level" => 0);
    ob_start(); include SERVER_VIEW_DIR."editor_info_block.html";
    return ob_get_clean();
  }
}

 ?>
