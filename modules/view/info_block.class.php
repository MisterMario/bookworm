<?php

namespace PageBuilder;

use DB;


class InfoBlock {
  public static function getBlocksListHTML($user_level) {
    $blocks_list = "";
    $db = DB::getInstance();
    $res = $db->query("SELECT title, content FROM ".DB_TABLES["i-block"]." WHERE access_level <= ".$user_level);
    if (gettype($res) == "boolean" || $res->num_rows == 0) return null;
    while($row = $res->fetch_assoc()) {
      ob_start();
      $block_title = $row["title"];
      $block_content = htmlspecialchars_decode($row["content"], ENT_QUOTES);
      include SERVER_VIEW_DIR."info_block.html";
      $blocks_list .= ob_get_clean();
    }

    return $blocks_list;
  }
}

 ?>
