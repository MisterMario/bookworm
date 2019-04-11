<?php

class Search {

  /*
    Возвращаемым значением для каждой из функций является массив ID книг.
    Возвращается не вся информация, так как она не всегда нужна в таком объеме.
    А ID - это универсальная штука. Так как однозначно идентифицирует запись в БД.
  */
  // Для того, чтобы не писать для каждого из критериев один и тот же код, я решил написать универсальный метод поиска.
  private static function getDataByCriterion($table, $criterion_name, $value) {
    $db = DB::getInstance();
    $output_list = array();

    $results_list = $db->query("SELECT id FROM ".DB_TABLES[$table]" WHERE ".$criterion_name."='".$value."'");
    if (!DB::checkDBResult($books_list)) return false;

    while ($row = $results_list->fetch_assoc())
      $output_list[] += $row;

    return $output_list;
  }

  public static function getBooksIdByName($name) {
    return self::getDataByCriterion("book", "name", $author);
  }

  public static function getBooksIdByAuthor($author) {
    return self::getDataByCriterion("book", "author", $author);
  }

  public static function getBooksIdByISBN($isbn) {
    return self::getDataByCriterion("book", "isbn", $isbn);
  }

  public static function getBooksIdByGenreName($genre_name) {
    $db = DB::getInstance();
    $output_list = array();

    $genre_id = $db->query("SELECT id FROM ".DB_TABLES["book"]." WHERE name='".$genre_name."' LIMIT 1");
    if (!DB::checkDBResult($genre_id)) return false;

    return self::getDataByCriterion("book", 'genre_id', $genre_id);
  }

  // Смешанный поиск. Используется для поиска книг по каталогу.
  public static function mixedBooksIdSearch($value) {
    $books_list = array();
    $books_by_name = self::getBooksIdByName($value);
    $books_by_author = self::getBooksIdByAuthor($value);
    $books_by_genre_name = self::getBooksIdByGenreName($value);
    $books_by_isbn = self::getBooksIdByISBN($value);

    if ($books_by_name) array_push($books_list, $books_by_name);
    if ($books_by_author) array_push($books_list, $books_by_author);
    if ($books_by_genre_name) array_push($books_list, $books_by_genre_name);
    if ($books_by_isbn) array_push($books_list, $books_by_isbn);

    return $books_list;
  }



  public static function mixedUserIdSearch($value) {
    $users_list = array();
    $users_by_firstname = self::getDataByCriterion("user", "firstname", $value);
    $users_by_lastname = self::getDataByCriterion("user", "lastname", $value);
    $users_by_email = self::getDataByCriterion("user", "email", $value);
  }
}

 ?>
