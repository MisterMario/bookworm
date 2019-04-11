<?php

require_once("config.php");
require_once(MODULES_DIR."user.class.php");
require_once(MODULES_DIR."auth.class.php");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["logout"])) {
  $answer = array("code" => 2, "message" => "Ошибка! Переданы не все поля!", "html" => "");
} else {

  session_start();
  $login = trim($data["logout"]);
  $answer = null; // Хранит ответ сервера клиенту
  $user = Auth::createUser();

  if ($user) {
    Auth::sessionDestroy($user->getId());
    $user = Auth::createUser();
    $answer = array("code" => 0, "message" => "", "html" => array("mp" => PageBuilder\Page::getMiniProfil($user), "left_block" => PageBuilder\InfoBlock::getBlocksListHTML($user ? $user->getLevel() : 0)));
  } else $answer = array("code" => 2, "message" => "Вы не авторизованы!", "html" => "");
}

echo json_encode($answer);

?>
