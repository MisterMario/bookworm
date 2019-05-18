<?php

require_once("wr_config.php");
require_once(MODULES_DIR."user.class.php");
require_once(MODULES_DIR."auth.class.php");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["logout"])) {
  $answer = array("code" => 2, "message" => "Ошибка! Переданы не все поля!");
} else {

  session_start();
  $login = trim($data["logout"]);
  $answer = null; // Хранит ответ сервера клиенту
  $user = Auth::createUser();

  if ($user) {
    Auth::sessionDestroy($user->getId());
    $user = Auth::createUser();
    $answer = array("code" => 0, "message" => "");
  } else $answer = array("code" => 2, "message" => "Вы не авторизованы!");
}

echo json_encode($answer);

?>
