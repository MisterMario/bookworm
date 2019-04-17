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

session_start();
$user = Auth::createUser();

if ($data["mode"] == "book_edit") {
  $answer["message"] = Validate::bookInformation($data);
  if (mb_strlen($answer["message"], "utf-8") == 0) {
    $data["name"] = $data["title"];
    $answer["status"] = Book::edit($data);
  }

} elseif ($data["mode"] == "book_new") {
  $answer["message"] = Validate::bookInformation($data);
  if (mb_strlen($answer["message"], "utf-8") == 0) {
    $data["name"] = $data["title"];
    $answer["status"] = Book::add($data);
  }

} elseif ($data["mode"] == "i_block_edit") {
  $answer["message"] = Validate::infoBlock($data);
  if (mb_strlen($answer["message"], "utf-8") == 0)
    $answer["status"] = InfoBlock::edit($data);

} elseif ($data["mode"] == "i_block_new") {
  $answer["message"] = Validate::infoBlock($data);
  if (mb_strlen($answer["message"], "utf-8") == 0)
    $answer["status"] = InfoBlock::add($data);

} elseif ($data["mode"] == "my_contacts_edit") {
  $answer["message"] = Validate::userContacts($data, true);
  if (mb_strlen($answer["message"], "utf-8") == 0)
    $answer["status"] = $user->setData($data);

} elseif ($data["mode"] == "register") {
  $answer["message"] = Validate::registerForm($data, true);
  if (mb_strlen($answer["message"], "utf-8") == 0)
    $answer["status"] = User::add($data);

} elseif ($data["mode"] == "add_review") {
  $data["user_id"] = $user->getId(); // Нужно добавить проверку того, что пользователь авторизован
  $answer["message"] = Validate::review($data);
  if (mb_strlen($answer["message"], "utf-8") == 0) {
    $answer["status"] = Review::add($data);
    $answer["item_id"] = Review::getLastId();
  }

} elseif ($data["mode"] == "edit_review") {
  $data["user_id"] = $user->getId(); // Нужно добавить проверку того, что пользователь авторизован
  $answer["message"] = Validate::review($data);
  if (mb_strlen($answer["message"], "utf-8") == 0)
    $answer["status"] = Review::edit($data);

} elseif ($data["mode"] == "remove_review") { // Нужно перебросить этот метод куда-нибудь. Но  только не в валидатор.
  $data["user_id"] = $user->getId(); // Нужно добавить проверку того, что пользователь авторизован
  $answer["status"] = Review::removeById($data["id"]);

}

echo json_encode($answer);

 ?>
