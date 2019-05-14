<?php

// Скрипт принимает строку поиска от клиента
// и возвращает ему HTML со списком результатов поиска (список товаров)

$data = json_decode(file_get_contents("php://input"), true);
$answer = array("status" => false, "html" => "");


require_once("wr_config.php");
require_once(VIEW_MODULES_DIR."genre.class.php");
require_once(VIEW_MODULES_DIR."search.class.php");


if (isset($data["search_string"])) {

  $search_string = $data["search_string"];
  $db = DB::getInstance();

  // Получение ID жанра, если такой есть
  $genre_id = PageBuilder\Genre::getIdByName($search_string);

  $books = $db->query("SELECT id, name, author FROM ".DB_TABLES["book"].
                      " WHERE name LIKE '%${search_string}%'".
                      " OR author LIKE '%${search_string}%'".
                      ($genre_id != null ? " OR genre_id LIKE '${genre_id}'" : "").
                      " LIMIT 4");

  if (gettype($books) != "boolean" && $books->num_rows != 0) {
    $books_list = array();
    $i = 0;
    while ($book = $books->fetch_assoc()) {
      $books_list[$i] = $book;
      $i++;
    }
    $answer = array("status" => true, "html" => PageBuilder\Search::getResultsList($books_list));
  }
}

echo json_encode($answer);

 ?>
