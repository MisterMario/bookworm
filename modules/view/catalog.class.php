<?php

namespace PageBuilder;

require_once("book.class.php");

use DB;


class Catalog {
  public static function getHTML($page_info) { // Возвращает контент главной страницы (каталога всех товаров)
    $content = "";
    ob_start(); include SERVER_VIEW_DIR."sorting_goods.html";
    $content .= ob_get_clean();
    $content .= BooksCatalog::getFullBooksListHTML($page_info["page_num"], $page_info["item_code"]);

    $db = DB::getInstance();
    $num_pages = $db->query("SELECT COUNT(id) as count FROM ".DB_TABLES["book"].($page_info["page_code"] == 2 ? " WHERE genre_id=".$page_info["item_code"] : "") ); // Получение количества всех записей определенного жанра
    if (gettype($num_pages) == "boolean" || $num_pages->fetch_assoc()["count"] == 0) return EmptyContent::getHTML(2);
    $num_pages->data_seek(0);
    $num_pages = (int)ceil( ( (int)$num_pages->fetch_assoc()["count"] ) / 12 ); // Количество страниц

    if ($page_info["item_code"] == 0) $uri = "/catalog/";
    else $uri = "/genre/".$page_info["item_code"]."/";

    $content .= Page::getPageNavigation($num_pages, $page_info["page_num"], $uri);
    return $content;
  }
}

class BooksCatalog {
  public static function getFullBooksListHTML($page_num = 1, $genre_id = 0) {
    $books_list = ""; $books_row = ""; $i = 0; $first_id = 0;
    $db = DB::getInstance();

    if ($genre_id != 0) { // Если требуется получить книги конкретного жанра
      $first_id = $db->query("SELECT id FROM ".DB_TABLES["book"]." WHERE genre_id='".$genre_id."' LIMIT 1");
      if (gettype($first_id) != "boolean" || $first_id->num_rows != 0) $first_id = (int)$first_id->fetch_assoc()["id"]-1;
    }
    $first_id += ($page_num * 12) - 12 + 1; // Для того, чтобы получать книги только нужной страницы

    // Нужно сделать сортировку в обратном порядке, для того, чтобы сразу отображались самые новые товары, а затем старые
    $res = $db->query("SELECT id, name, author, price
                       FROM ".DB_TABLES["book"].
                       " WHERE id >= ".$first_id.
                       ($genre_id != 0 ? " AND genre_id=".$genre_id : "")." LIMIT 12"); // Если нужно выбирать книги конкретного жанра
    if (gettype($res) == "boolean" || $res->num_rows == 0) return null;
    while($row = $res->fetch_assoc()) {
      $i++;
      ob_start();
      $book = array("id" => $row["id"], "name" => Book::bookName($row["name"]), "author" => Book::bookAuthor($row["author"]), "price" => $row["price"], "image" => Book::getImage($row["id"]));
      include SERVER_VIEW_DIR."small_book.html";
      $books_row .= ob_get_clean();
      if ($i % 4 == 0 || $i == $res->num_rows) { // Строка добавляется в каталог неполной, если книга последняя.
        ob_start();
        include SERVER_VIEW_DIR."small_books_row.html";
        $books_list .= ob_get_clean(); $books_row = ""; // Добавляет текущую строку с книгами в каталог и очишает ее
      }
    }
    ob_start(); include SERVER_VIEW_DIR."catalog.html";

    return ob_get_clean();
  }
}

 ?>
