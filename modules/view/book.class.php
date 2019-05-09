<?php

namespace PageBuilder;

require_once("genre.class.php");
require_once("review.class.php");

use DB;


class Book {
  public static function getFullInfoHTML($page_info) {
    global $user;

    $content = "";

    $book_general_information = self::getFullBookInfoHTML($page_info["item_code"]);
    $book_reviews = Review::getReviewsList($page_info["item_code"]);
    $my_review = "";
    if ($user) {
      ob_start(); include SERVER_VIEW_DIR."my_review.html";
      $my_review = ob_get_clean();
    }

    ob_start(); include SERVER_VIEW_DIR."book_info.html";
    return ob_get_clean();
  }

  public static function getFullBookInfoHTML($id) { // Возвращает блок с полной информацией о книге
    $db = DB::getInstance();
    $res = $db->query("SELECT * FROM ".DB_TABLES["book"]." WHERE id=".$id." LIMIT 1");
    if(gettype($res) == "boolean" || $res->num_rows == 0) return null;
    $res = $res->fetch_assoc();
    foreach($res as $key => $value){
      $book[$key] = ($value != null)? $value : "не указан.";
    }
    $book["genre"] = Genre::getName($book["genre_id"]);
    $book["image"] = self::getImage($book["id"]);
    ob_start();
    include SERVER_VIEW_DIR."book_general_information.html";
    return ob_get_clean();
  }

  public static function getImage($id) { // На случай, если для книги не было загружено картинки
    return file_exists(SERVER_VIEW_DIR."/products/".$id.".png")? $id.".png" : "default.png";
  }

  public static function bookName($name) {
    return mb_strlen($name, "utf-8") > 20 ? mb_substr($name, 0, 19, "utf-8")."..." : $name;
  }

  public static function bookAuthor($author) { // Усекает имя автора, если оно больше определенного
    return mb_strlen($author, "utf-8") > 25 ? mb_substr($author, 0, 22, "utf-8")."..." : $author;
  }
}

 ?>
