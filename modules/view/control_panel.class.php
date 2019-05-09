<?php

namespace PageBuilder;

require_once("book.class.php");

use DB;


class ControlPanel {

  public static function getMenuHTML() {
    ob_start(); include SERVER_VIEW_DIR."control-panel.html";
    return ob_get_clean();
  }

  public static function getBooksListHTML($page_num, $count) {
    $db = DB::getInstance();
    $list = "";

    $offset = ($page_num * $count) - $count;
    $res = $db->query("SELECT id, name, author, price, count FROM ".DB_TABLES["book"]." ORDER BY id LIMIT ".$count." OFFSET ".$offset);
    if (gettype($res) == "boolean" || $res->num_rows == 0) return null;

    while ($row = $res->fetch_assoc()) {
      $book = array("id" => $row["id"], "name" => $row["name"], "author" => $row["author"], "price" => $row["price"], "count" => $row["count"]);
      $book["total_sum"] = (int)$book["price"] * (int)$book["count"];
      $book["image"] = Book::getImage($book["id"]);
      ob_start(); include SERVER_VIEW_DIR."cp_small_book.html";
      $list .= ob_get_clean();
    }
    $num_pages = (int)( $db->query("SELECT count(id) FROM ".DB_TABLES["book"])->fetch_assoc()["count(id)"] ) / $count;
    $num_pages = (int)ceil($num_pages);
    $pages_navigation = Page::getPageNavigation($num_pages, $page_num, "/control/products/");

    ob_start(); include SERVER_VIEW_DIR."cp_books.html";
    return ob_get_clean();
  }

  public static function getUsersListHTML($page_num, $count) {
    $db = DB::getInstance();
    $list = "";

    $offset = ($page_num * $count) - $count;
    $res = $db->query("SELECT id, firstname, lastname, email FROM ".DB_TABLES["user"]." ORDER BY id LIMIT ".$count." OFFSET ".$offset);
    if (gettype($res) == "boolean" || $res->num_rows == 0) return null;

    while ($row = $res->fetch_assoc()) {
      $user_info = array("id" => $row["id"], "firstname" => $row["firstname"], "lastname" => $row["lastname"], "email" => $row["email"]);
      ob_start(); include SERVER_VIEW_DIR."cp_small_user.html";
      $list .= ob_get_clean();
    }
    $num_pages = (int)( $db->query("SELECT count(id) FROM ".DB_TABLES["user"])->fetch_assoc()["count(id)"] ) / $count;
    $num_pages = (int)ceil($num_pages);
    $pages_navigation = Page::getPageNavigation($num_pages, $page_num, "/control/users/");

    ob_start(); include SERVER_VIEW_DIR."cp_users.html";
    return ob_get_clean();
  }

  public static function getInfoBlocksListHTML($page_num, $count) {
    $db = DB::getInstance();
    $list = "";

    $offset = ($page_num * $count) - $count;
    $res = $db->query("SELECT id, title, access_level FROM ".DB_TABLES["i-block"]." ORDER BY id LIMIT ".$count." OFFSET ".$offset);
    if (gettype($res) == "boolean" || $res->num_rows == 0) return null;

    while ($row = $res->fetch_assoc()) {
      $ib = array("id" => $row["id"], "title" => $row["title"], "access_level" => $row["access_level"]);
      ob_start(); include SERVER_VIEW_DIR."cp_small_info_block.html";
      $list .= ob_get_clean();
    }
    $num_pages = (int)( $db->query("SELECT count(id) FROM ".DB_TABLES["i-block"])->fetch_assoc()["count(id)"] ) / $count;
    $num_pages = (int)ceil($num_pages);
    $pages_navigation = Page::getPageNavigation($num_pages, $page_num, "/control/info-blocks/");

    ob_start(); include SERVER_VIEW_DIR."cp_info_blocks.html";
    return ob_get_clean();
  }

  // $count - число пользователей, отображаемых на одной странице
  public static function getSearchResultsByUsers($searched_string, $page_num, $count) {
    $db = DB::getInstance();
    $list = "";

    $offset = ($page_num * $count) - $count;
    $selection = $db->query("SELECT id, firstname, lastname, email FROM ".DB_TABLES["user"]." WHERE ".
                            "firstname LIKE '%${searched_string}%' OR ".
                            "lastname LIKE '%${searched_string}%' OR ".
                            "email LIKE '%${searched_string}%' ".
                            "ORDER BY id LIMIT ${count} OFFSET ${offset}");
    if (!DB::checkDBResult($selection)) return null;

    while ($row = $selection->fetch_assoc()) {
      $user_info = array("id" => $row["id"], "firstname" => $row["firstname"],
                         "lastname" => $row["lastname"], "email" => $row["email"]);
      ob_start(); include SERVER_VIEW_DIR."cp_small_user.html";
      $list .= ob_get_clean();
    }

    $num_pages = (int)( $db->query("SELECT count(id) FROM ".DB_TABLES["user"]." WHERE ".
                                   "firstname LIKE '%${searched_string}%' OR ".
                                   "lastname LIKE '%${searched_string}%' OR ".
                                   "email LIKE '%${searched_string}%'")->fetch_assoc()["count(id)"] ) / $count;
    $num_pages = (int)ceil($num_pages);
    $pages_navigation = Page::getPageNavigation($num_pages, $page_num, "/control/users/search/${searched_string}/");

    ob_start(); include SERVER_VIEW_DIR."cp_users.html";
    return ob_get_clean();
  }

  public static function getSearchResultsByProducts($searched_string, $page_num, $count) {
    $db = DB::getInstance();
    $list = "";

    $offset = ($page_num * $count) - $count;
    $selection = $db->query("SELECT id, name, author, price, count FROM ".DB_TABLES["book"]." WHERE ".
                      "name LIKE '%${searched_string}%' OR ".
                      "author LIKE '%${searched_string}%' ".
                      "ORDER BY id LIMIT ${count} OFFSET ${offset}");

    if (!DB::checkDBResult($selection)) return null;

    while ($row = $selection->fetch_assoc()) {
      $book = array("id" => $row["id"], "name" => $row["name"], "author" => $row["author"],
                    "price" => $row["price"], "count" => $row["count"],
                    "total_sum" => (int)$row["price"] * (int)$row["count"],
                    "image" => Book::getImage($row["id"]));
      ob_start(); include SERVER_VIEW_DIR."cp_small_book.html";
      $list .= ob_get_clean();
    }

    $num_pages = (int)( $db->query("SELECT count(id) FROM ".DB_TABLES["book"]." WHERE ".
                                   "name LIKE '%${searched_string}%' OR ".
                                   "author LIKE '%${searched_string}%' ")->fetch_assoc()["count(id)"] ) / $count;
    $num_pages = (int)ceil($num_pages);
    $pages_navigation = Page::getPageNavigation($num_pages, $page_num, "/control/products/search/${searched_string}/");

    ob_start(); include SERVER_VIEW_DIR."cp_books.html";
    return ob_get_clean();
  }

  public static function getSearchResultsByInfoBlocks($searched_string, $page_num, $count) {
    $db = DB::getInstance();
    $list = "";

    $offset = ($page_num * $count) - $count;
    $selection = $db->query("SELECT id, title, access_level FROM ".DB_TABLES["i-block"]." WHERE ".
                            "title LIKE '%${searched_string}%' ".
                            "ORDER BY id LIMIT ".$count." OFFSET ".$offset);
    if (!DB::checkDBResult($selection)) return null;

    while ($row = $selection->fetch_assoc()) {
      $ib = array("id" => $row["id"], "title" => $row["title"], "access_level" => $row["access_level"]);
      ob_start(); include SERVER_VIEW_DIR."cp_small_info_block.html";
      $list .= ob_get_clean();
    }

    $num_pages = (int)( $db->query("SELECT count(id) FROM ".DB_TABLES["i-block"]." ".
                                   "WHERE title LIKE '%${searched_string}%' ")->fetch_assoc()["count(id)"] ) / $count;
    $num_pages = (int)ceil($num_pages);
    $pages_navigation = Page::getPageNavigation($num_pages, $page_num, "/control/info-blocks/search/${searched_string}/");

    ob_start(); include SERVER_VIEW_DIR."cp_info_blocks.html";
    return ob_get_clean();
  }
}

 ?>
