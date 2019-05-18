<?php

require_once("wr_config.php");
require_once(MODULES_DIR."user.class.php");
require_once(MODULES_DIR."auth.class.php");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["login"]) || !isset($data["password"]) || !isset($data["remember"])) {
  $answer = array("code" => 2, "message" => "Ошибка! Переданы не все поля!");
} else {

  session_start();
  $login = trim($data["login"]);
  $password = $data["password"];
  $remember = $data["remember"] == 1 ? true: false;
  $answer = null; // Хранит ответ сервера клиенту
  $user = Auth::createUser();

  if ($user) {
    $answer = array("code" => 2, "message" => "Ошибка! Вы уже авторизованы!");
  } elseif (empty($login) || empty($password)) {
    $answer = array("code" => 2, "message" => "Ошибка! Не все поля заполнены!");
  } else {

    $db = DB::getInstance();
    $user_id = $db->query("SELECT id FROM ".DB_TABLES["user"].
                          " WHERE email='$login' AND password='".$password."' LIMIT 1");
    if (gettype($user_id) != "boolean" && $user_id->num_rows != 0) {

      $user_id = $user_id->fetch_assoc();
      $user = new User("id", $user_id["id"]);
      Auth::sessionCreate($user->getDataArr(), $remember);
      ob_start();
      // Необходимо получить левый блок и мини-профиль
      $answer = array("code" => 0, "message" => "Успешный вход!");

    } else $answer = array("code" => 2, "message" => "Ошибка! Неверный логин или пароль!");
  }

}

echo json_encode($answer);

?>
