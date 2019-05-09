<?php

namespace PageBuilder;

use DB;


class Genre {
  public static function getName($id) {
    $db = DB::getInstance();
    $res = $db->query("SELECT name FROM ".DB_TABLES["genre"]." WHERE id=".$id." LIMIT 1");
    if(gettype($res) == "boolean" || $res->num_rows == 0) return null;

    return $res->fetch_assoc()["name"];
  }

  public static function getIdByName($name) {
    $db = DB::getInstance();
    $genre_id = $db->query("SELECT id FROM ".DB_TABLES["genre"]." WHERE name LIKE '%${name}%' LIMIT 1");
    if (gettype($genre_id) == "boolean" || $genre_id->num_rows == 0) return null;

    return $genre_id->fetch_assoc()["id"];
  }

  public static function getGenreSelectHTML($selected_num = -1) {
    $list = "";
    $db = DB::getInstance();

    $res = $db->query("SELECT id, name FROM ".DB_TABLES["genre"]);
    if(gettype($res) == "boolean" || $res->num_rows == 0) return null;

    while($row = $res->fetch_assoc()) {
      if ((int)$selected_num == (int)$row["id"]) $list .= "<option value=\"".$row["id"]."\" selected>".$row["name"]."</option>";
      else $list .= "<option value=\"".$row["id"]."\">".$row["name"]."</option>";
    }

    return $list;
  }
}

 ?>
