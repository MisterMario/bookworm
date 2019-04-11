<?php

/*
  Класс для работы с куками и сессией взят из моего проекта "Личный кабинет на AJAX"
  (c) Mr_Mario
*/

class Auth {

  /* Функция для генерации случайной строки */
  public static function TmpGenerate($tmp_length = 32){
  	$allchars = "abcdefghijklmnopqrstuvwxyz0123456789";
  	$output = "";
    mt_srand( (double) microtime() * 1000000 );
  	for($i = 0; $i < $tmp_length; $i++){
  	   $output .= $allchars{ mt_rand(0, strlen($allchars)-1) };
  	}
  	return $output;
  }
  /* Создает сессию и куки для пользователя */
  public static function sessionCreate($data, $remember = true) {
    if (!isset($_SESSION["user"]) && !isset($_COOKIE["bw-v1-id"])) { // Если сессия и куки не созданы
      $_SESSION["user"] = $data;

      if ($remember) {
        $tmp = self::TmpGenerate();

        $db = DB::getInstance();
        if (!$db->query("UPDATE ".DB_TABLES["user"]." SET tmp='$tmp' WHERE id=".$data["id"]." LIMIT 1")) return false;

        setcookie("bw-v1-id", $data["id"], time()+3600*24*30, "/");
        setcookie("bw-v1-tmp", $tmp, time()+3600*24*30, "/");
      }

      return true;
    }
    return false;
  }
  /* Уничтожает сессию и куки пользователя */
  public static function sessionDestroy($id) {
    session_unset();
    session_destroy();
    if (isset($_COOKIE["bw-v1-id"]) && isset($_COOKIE["bw-v1-tmp"])) {
      setcookie("bw-v1-id", "", time()-3600, "/");
      setcookie("bw-v1-tmp", "", time()-3600, "/");

      $db = DB::getInstance();
      $db->query("UPDATE ".DB_TABLES["user"]." SET tmp='' WHERE id=".$id." LIMIT 1");
    }
  }
  public static function createUser() {

    if (isset($_SESSION["user"])) { // Получение данных о пользователе из сессии
      return new User("data", $_SESSION["user"]);
    } elseif (isset($_COOKIE["bw-v1-id"]) && isset($_COOKIE["bw-v1-tmp"])) { // Получение даннных о пользователе из кук

      $db = DB::getInstance();
      $user_id = $db->query("SELECT id FROM ".DB_TABLES["user"].
                                      " WHERE id='".$_COOKIE["bw-v1-id"]."'".
                                      " AND tmp='".$_COOKIE["bw-v1-tmp"]."'");

      if (gettype($user_id) != "boolean" && $user_id->num_rows != 0) {
        $user_id = $user_id->fetch_assoc()["id"];
        $user = new User("id", $user_id);
        $_SESSION["user"] = $user->getDataArr();

        return $user;
      } else { // Если информация из кук устарела или ложная
        setcookie("bw-v1-id", "", time()-3600, "/");
        setcookie("bw-v1-tmp", "", time()-3600, "/");
      }
    }
    return false;
  }

  public static function editCurrentUser($data) { // Обновляет сессию текущего пользователя
    if (isset($_SESSION["user"])) {
      $_SESSION["user"] = $data;
    }
  }
}

?>
