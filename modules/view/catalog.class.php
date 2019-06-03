<?php

namespace PageBuilder;

/* Может содержать костыли и немного быдлокода.
   Впоследствии я это исправлю. Сейчас сильно ограничен во времени и приходится делать что есть. */

require_once("book.class.php");

use DB;


class Catalog {
  public static function getHTML($page_info) { // Возвращает контент главной страницы (каталога всех товаров)
    $content = ""; $genre_id = 0; $order_method = "";
    $order_by = ""; $order_method_name= "";

    switch ($page_info["page_code"]) {
      case 1:
        $link = "catalog";
        break;
      case 2:
        $genre_id = $page_info["item_code"];
        $link = "genre/${genre_id}";
        break;
      case 19:
        $order_method = $page_info["item_code"];
        $link = "catalog";
        break;
      case 20:
        $genre_id = $page_info["category_num"];
        $order_method = $page_info["item_code"];
        $link = "genre/${genre_id}";
        break;
    }

    if ($order_method != "")
      $order_method_name = $page_info["item_code"];

    ob_start(); include SERVER_VIEW_DIR."sorting_goods.html";
    $content .= ob_get_clean();

    switch ($order_method) {
      case "alphabet":
        $order_by = "name";
        $order_method = "ASC";
        break;
      case "price-asc":
        $order_by = "price";
        $order_method = "ASC";
        break;
      case "price-desc":
        $order_by = "price";
        $order_method = "DESC";
        break;
    }

    $content .= BooksCatalog::getFullBooksListHTML($page_info["page_num"], $genre_id, $order_by, $order_method);

    $db = DB::getInstance();
    $num_pages = $db->query("SELECT COUNT(id) as count FROM ".DB_TABLES["book"].
                            ($genre_id != 0 ? " WHERE genre_id='${genre_id}'" : ""). // Получение количества всех записей определенного жанра
                            ($order_method != "" ? " ORDER BY ${order_by} ${order_method}" : "") );
    if (!DB::checkDBResult($num_pages)) return EmptyContent::getHTML(2);

    $num_pages->data_seek(0);
    $num_pages = (int)ceil( ( (int)$num_pages->fetch_assoc()["count"] ) / 12 ); // Количество страниц

    if ($genre_id == 0) $uri = "/catalog/";
    else $uri = "/genre/${genre_id}/";
    if ($order_method != "") $uri = "/sorted".$uri."${page_info["item_code"]}/";

    $content .= Page::getPageNavigation($num_pages, $page_info["page_num"], $uri);
    return $content;
  }
}

class BooksCatalog {
  public static function getFullBooksListHTML($page_num = 1, $genre_id = 0, $order_by = "", $order_method = "") {
    $books_list = ""; $books_row = ""; $i = 0; $offset = 0;
    $db = DB::getInstance();

    $offset += ($page_num * 12) - 12; // Для того, чтобы получать книги только нужной страницы

    // Нужно сделать сортировку в обратном порядке, для того, чтобы сразу отображались самые новые товары, а затем старые
    $book_selection = $db->query("SELECT id, name, author, price
                                 FROM ".DB_TABLES["book"].
                                 ($genre_id != 0 ? " WHERE genre_id=".$genre_id : "").
                                 ($order_by != "" ? " ORDER BY ${order_by} ${order_method}" : "").
                                 " LIMIT 12 OFFSET ${offset}"); // Если нужно выбирать книги конкретного жанра
    if (gettype($book_selection) == "boolean" || $book_selection->num_rows == 0) return null;

    while($book = $book_selection->fetch_assoc()) {
      $i++;
      ob_start();
      $book = array("id" => $book["id"], "name" => Book::bookName($book["name"]), "author" => Book::bookAuthor($book["author"]), "price" => $book["price"], "image" => Book::getImage($book["id"]));
      include SERVER_VIEW_DIR."small_book.html";
      $books_row .= ob_get_clean();
      if ($i % 4 == 0 || $i == $book_selection->num_rows) { // Строка добавляется в каталог неполной, если книга последняя.
        ob_start();
        include SERVER_VIEW_DIR."small_books_row.html";
        $books_list .= ob_get_clean(); $books_row = ""; // Добавляет текущую строку с книгами в каталог и очишает ее
      }
    }
    ob_start(); include SERVER_VIEW_DIR."catalog.html";

    return ob_get_clean();
  }

  public static function getNewBooksList() {
    $content = ""; $content .= ob_get_clean();
    $db = DB::getInstance();

    $count_selection = $db->query("SELECT COUNT(id) AS count FROM ".DB_TABLES["book"]); // Получение количества всех записей определенного жанра
    if (!DB::checkDBResult($count_selection) ||
        (int)$count_selection->fetch_assoc()["count"] == 0) return EmptyContent::getHTML(2);

    $content .= BooksCatalog::getFullBooksListHTML(1, 0, "id", "DESC");

    return $content;
  }
}

 ?>
