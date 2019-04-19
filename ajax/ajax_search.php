<?php

// Скрипт принимает строку поиска от клиента
// и возвращает ему HTML со списком результатов поиска (список товаров)

$data = json_decode(file_get_contents("php://input"), true);
$answer = array("status" => false, "html" => "");

require_once("wr_config.php");

if (isset($data["search_string"])) {

  $search_string = $data["search_string"];
  $db = DB::getInstance();

  // Получение ID жанра, если такой есть
  $genre_id = $db->query("SELECT id FROM ".DB_TABLES["genre"]." WHERE name LIKE '%${search_string}%' LIMIT 1");
  if (gettype($genre_id) != "boolean" && $genre_id->num_rows != 0)
    $genre_id = $genre_id->fetch_assoc()["id"];
  else $genre_id = "";

  $books = $db->query("SELECT id, name, author FROM ".DB_TABLES["book"].
                      " WHERE name LIKE '%${search_string}%'".
                      " OR author LIKE '%${search_string}%'".
                      (strlen($genre_id) != 0 ? " OR genre_id LIKE '${genre_id}'" : "").
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
