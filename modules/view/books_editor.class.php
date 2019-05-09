<?php

namespace PageBuilder;

require_once("genre.class.php");
require_once("book.class.php");

use DB;


class BooksEditor {
  public static function getEditHTML($id) {
    $db = DB::getInstance();
    $isEdit = true;

    $res = $db->query("SELECT * FROM ".DB_TABLES["book"]." WHERE ".DB_TABLES["book"].".id='".$id."' LIMIT 1");
    if (gettype($res) == "boolean" || $res->num_rows == 0) return null;
    $res = $res->fetch_assoc();

    $book = array("id" => $res["id"], "name" => $res["name"], "author" => $res["author"],
                  "language" => $res["language"], "keywords" => $res["tags"], "series" => $res["series"],
                  "rightholder" => $res["rightholder"], "age_restrictions" => $res["age_restrictions"], "isbn" => $res["isbn"],
                  "price" => $res["price"], "count" => $res["count"], "annotation" => $res["annotation"], "image" => Book::getImage($res["id"]));
    $genre_select = Genre::getGenreSelectHTML($res["genre_id"]);

    ob_start(); include SERVER_VIEW_DIR."editor_book.html";
    return ob_get_clean();
  }

  public static function getNewHTML() {
    $isEdit = false;

    $book = array("id" => 0, "name" => "", "author" => "", "genre" => "", "language" => "", "keywords" => "", "series" => "",
                  "rightholder" => "", "age_restrictions" => "", "isbn" => "", "price" => "", "count" => "", "annotation" => "", "image" => Book::getImage(0));
    $genre_select = Genre::getGenreSelectHTML();
    ob_start(); include SERVER_VIEW_DIR."editor_book.html";
    return ob_get_clean();
  }
}

 ?>
