<?php

namespace PageBuilder;

require_once("genre.class.php");
require_once("book.class.php");

use DB;


class Search {
  public static function getBooksBySearchString($searched_string, $page_num) {
    $content = "";
    $db = DB::getInstance();

    ob_start(); include SERVER_VIEW_DIR."sorting_goods.html";
    $content .= ob_get_clean();

    $offset = ($page_num * 12) - 12;
    $genre_id = Genre::getIdByName($searched_string);
    $searched_books = $db->query("SELECT id, name, author, price FROM ".DB_TABLES["book"].
                                 " WHERE name LIKE '%${searched_string}%'".
                                 " OR author LIKE '%${searched_string}%'".
                                 (strlen($genre_id) != 0 ? " OR genre_id LIKE '${genre_id}'" : "").
                                 " LIMIT 12 OFFSET ${offset}");
    if (gettype($searched_books) == "boolean" || $searched_books->num_rows == 0) return EmptyContent::getHTML(4);

    $books_list = ""; $books_row = ""; $i = 1;
    while ($book = $searched_books->fetch_assoc()) { // Формирование списка книг
      ob_start();
      $book["name"] = Book::bookName($book["name"]);
      $book["author"] = Book::bookAuthor($book["author"]);
      $book["image"] = Book::getImage($book["id"]);
      include SERVER_VIEW_DIR."small_book.html";
      $books_row .= ob_get_clean();

      if ($i % 4 == 0 || $i == $searched_books->num_rows) { // Строка добавляется в каталог неполной, если книга последняя.
        ob_start();
        include SERVER_VIEW_DIR."small_books_row.html";
        $books_list .= ob_get_clean(); $books_row = ""; // Добавляет текущую строку с книгами в каталог и очишает ее
      }
      $i++;
      if ($i > 4) $i = 1;
    }
    ob_start(); include SERVER_VIEW_DIR."catalog.html";
    $content .= ob_get_clean();

    $searched_books_num = $db->query("SELECT COUNT(id) as count FROM ".DB_TABLES["book"].
                                     " WHERE name LIKE '%${searched_string}%'".
                                     " OR author LIKE '%${searched_string}%'".
                                     (strlen($genre_id) != 0 ? " OR genre_id LIKE '${genre_id}'" : ""));
    if (gettype($searched_books_num) == "boolean" || $searched_books_num->num_rows == 0) return EmptyContent::getHTML(4);
    $num_pages = (int)ceil( $searched_books_num->fetch_assoc()["count"] / 12 ); // Количество страниц

    $uri = "/search/".$searched_string."/";

    $content .= Page::getPageNavigation($num_pages, $page_num, $uri);
    return $content;
  }

  public static function getResultsList($books_list) {
    $html = "";

    foreach ($books_list as $book) {
      $book["image"] = Book::getImage($book["id"]);
      if (mb_strlen($book["name"], "utf-8") > 25)
        $book["name"] = mb_substr($book["name"], 0, 23, "utf-8")."...";
      if (mb_strlen($book["author"], "utf-8") > 30)
        $book["author"] = mb_substr($book["author"], 0, 30, "utf-8")."...";
      ob_start(); include SERVER_VIEW_DIR."search-result-line.html";
      $html .= ob_get_clean();
    }
    return $html;
  }
}

 ?>
