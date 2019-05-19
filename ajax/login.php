<?php

require_once("wr_config.php");
require_once(MODULES_DIR."user.class.php");
require_once(MODULES_DIR."auth.class.php");
require_once(MODULES_DIR."cryptor.class.php");

$data = json_decode(file_get_contents("php://input"), true);
$answer = array("code" => 2, "message" => "");

if (!isset($data["login"]) || !isset($data["password"]) || !isset($data["remember"])) {

  $answer["message"] = "Ошибка! Переданы не все поля!";

} else {

  session_start();
  $login = trim($data["login"]);
  $password = $data["password"];
  $remember = $data["remember"] == 1 ? true: false;
  $answer = null; // Хранит ответ сервера клиенту
  $user = Auth::createUser();

  if ($user) {

    $answer["message"] = "Ошибка! Вы уже авторизованы!";

  } elseif (empty($login) || empty($password)) {

    $answer["message"] = "Ошибка! Не все поля заполнены!";

  } else {

    $db = DB::getInstance();
    $user_info = $db->query("SELECT id, password FROM ".DB_TABLES["user"].
                          " WHERE email='${login}' LIMIT 1");

    if (DB::checkDBResult($user_info)) {

      $user_info = $user_info->fetch_assoc();

      if (Cryptor::confirmPasswords($password, $user_info["password"])) {

        $user = new User("id", $user_info["id"]);
        Auth::sessionCreate($user->getDataArr(), $remember);
        ob_start();
        // Необходимо получить левый блок и мини-профиль
        $answer = array("code" => 0, "message" => "Успешный вход!");

      } else $answer["message"] = "Ошибка! Введен неверный пароль!";

    } else $answer["message"] = "Ошибка! Такого пользователя не существует!";
  }

}

echo json_encode($answer);

?>
