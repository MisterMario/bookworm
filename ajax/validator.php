<?php

/*
  Скрипт принимает данные от некоторых форм, валидирует их и приозводит соответствующие действия.
  Только для форм с отправкой данных, с использованием AJAX
*/

$data = json_decode(file_get_contents("php://input"), true);
$answer = array("message" => "Ошибка!", "status" => false);

require_once("wr_config.php");
require_once(MODULES_DIR."validate.class.php");
require_once(MODULES_DIR."book.class.php");
require_once(MODULES_DIR."info_block.class.php");
require_once(MODULES_DIR."review.class.php");
require_once(MODULES_DIR."genre.class.php");

session_start();
$user = Auth::createUser();

if ($data["mode"] == "register" && !$user) {

  $answer["message"] = Validate::registerForm($data, true, true);
  if (mb_strlen($answer["message"], "utf-8") == 0) {
    $data["level"] = 2;
    $answer["status"] = User::add($data);
  }

} elseif (!$user) {

  $answer["message"] = "Этот запрос недоступен неавторизованному пользователю!";

} elseif ($data["mode"] == "my_contacts_edit") {

  $data["id"] = $user->getId(); // При валидации данных в userContacts() требуется ID пользователя
  $answer["message"] = Validate::userContacts($data, true);
  if (mb_strlen($answer["message"], "utf-8") == 0)
    $answer["status"] = $user->setData($data);

} elseif ($data["mode"] == "add_review") {

  $data["user_id"] = $user->getId();
  $answer["message"] = Validate::review($data);
  if (mb_strlen($answer["message"], "utf-8") == 0) {
    $answer["status"] = Review::add($data);
    $answer["item_id"] = Review::getLastId();
  }

} elseif ($data["mode"] == "edit_review") {

  $data["user_id"] = $user->getId();
  $answer["message"] = Validate::review($data);
  if (mb_strlen($answer["message"], "utf-8") == 0)
    $answer["status"] = Review::edit($data);

} elseif ($data["mode"] == "remove_review") { // Нужно перебросить этот метод куда-нибудь. Но  только не в валидатор.

  $data["user_id"] = $user->getId();
  $answer["status"] = Review::removeById($data["id"]);

} elseif ($user->getLevel() < 4) {

  $answer["message"] = "Этот запрос вам недоступен (нет прав)!";

} else if ($data["mode"] == "book_edit") {

  $answer["message"] = Validate::bookInformation($data);
  if (mb_strlen($answer["message"], "utf-8") == 0) {
    $data["name"] = $data["title"];
    $answer["status"] = Book::edit($data);
  }

} elseif ($data["mode"] == "book_new") {

  $answer["message"] = Validate::bookInformation($data);
  if (mb_strlen($answer["message"], "utf-8") == 0) {

    $data["name"] = $data["title"];
    $new_item_id = Book::add($data);

    if ($new_item_id){
      $answer["status"] = true;
      $answer["message"] = $new_item_id;
    }
    else
      $answer["message"] = "Серверная ошибка при добавлении товара!";
  }

} elseif ($data["mode"] == "i_block_edit") {

  $answer["message"] = Validate::infoBlock($data);
  if (mb_strlen($answer["message"], "utf-8") == 0)
    $answer["status"] = InfoBlock::edit($data);

} elseif ($data["mode"] == "i_block_new") {

  $answer["message"] = Validate::infoBlock($data);
  if (mb_strlen($answer["message"], "utf-8") == 0)
    $answer["status"] = InfoBlock::add($data);

} elseif ($data["mode"] == "genre_edit") {

  $answer["message"] = Validate::genre($data);
  if (mb_strlen($answer["message"], "utf-8") == 0)
    $answer["status"] = Genre::edit($data);

} elseif ($data["mode"] == "genre_new") {

  $answer["message"] = Validate::genre($data);
  if (mb_strlen($answer["message"], "utf-8") == 0)
    $answer["status"] = Genre::add($data);

}  elseif ($data["mode"] == "add_user" || $data["mode"] == "edit_user") {

  if (!empty($data["state"]) || !empty($data["city"]) ||
      !empty($data["address"]) || !empty($data["zip_code"]))
    $answer["message"] = Validate::userContacts($data, true, $data["mode"] == "add_user" ? true : false);
  else
    $answer["message"] = Validate::registerForm($data, $data["mode"] == "add_user" ? true : false, true);

  if ($answer["message"] == "")
    if ($data["mode"] == "add_user")
      $answer["status"] = User::add($data);
    else
      $answer["status"] = User::edit($data);

}


echo json_encode($answer);

 ?>
