<?php

class Validate {
  public static function registerForm($data, $checkPassword = true) {
    $msg = "";

    if (empty($data["firstname"]) || empty($data["lastname"]) || empty($data["email"]) ||
        empty($data["phone_number"]) || ($checkPassword && empty($data["password"])) || ($checkPassword && empty($data["repassword"]))) {
      $msg = "Ошибка! Не все поля заполнены!";

    } elseif (!preg_match("/^[a-zA-Zа-яА-Я]{1,}$/u", $data["firstname"])) {
      $msg = "Ошибка! Некорректное значение поля: имя!";

    } elseif(!preg_match("/^[a-zA-Zа-яА-Я]{1,}$/u", $data["lastname"])) {
      $msg = "Ошибка! Некорректное значения поля: фамилия!";

    } elseif (!preg_match("/^[a-zA-Z]{1,}[a-zA-Z0-9_]{0,}@(mail|gmail|)\.(ru|by|com)$/", $data["email"])) {
      $msg = "Ошибка! Некорректный e-mail!";

    } elseif (!preg_match("/^(\+){0,1}375(29|44)[0-9]{7}$/", $data["phone_number"])) {
      $msg = "Ошибка! Некорректный номер телефона!";

    } elseif ($checkPassword && !preg_match("/^[a-zA-Z0-9_\$]{1,}$/", $data["password"])) {
      $msg = "Ошибка! В пароле используются недопустимые символы!";

    } elseif ($data["gender"] != 1 && $data["gender"] != 2) {
      $msg = "Ошибка! Некорретное значение пола!";

    } elseif ($checkPassword && ($data["password"] != $data["repassword"])) {
      $msg = "Ошибка! Введенные пароли не совпадают!";

    } elseif ($checkPassword && (mb_strlen($data["password"], "utf-8") < 6 || mb_strlen($data["password"], "utf-8") > 64)) {
      $msg = "Ошибка! Пароль должен иметь длину от 6 до 64 символов!";

    }

    return $msg;
  }

  public static function userContacts($data, $isValidateUserInfo = false) {
    // Так как в массиве имеются все регистрационные данные, то можно не писать дублированный код
    $msg = $isValidateUserInfo ? self::registerForm($data, !empty($data["password"])) : "";
    if ($msg != "") return $msg; // Если на предыдущем этапе возникла ошибка - дальнейшая проверка бессмысленна

    // Так как данные поля не обязательны, то все дальнейшие условия бессмысленные при отсутсвии значений в полях
    if (!empty($data["state"]) || !empty($data["city"]) || !empty($data["address"]) || !empty($data["zip_code"])) {
      if (empty($data["state"]) || empty($data["city"]) || empty($data["address"]) || empty($data["zip_code"])) {
        $msg = "Ошибка! Не все поля заполнены!";

      } elseif (!preg_match("/^[а-яА-Яa-zA-Z]{1,}$/u", $data["state"])) {
        $msg = "Ошибка! Некорректное значение поля: область!";

      } elseif (!preg_match("/^[а-яА-Яa-zA-Z]{1,}$/u", $data["city"])) {
        $msg = "Ошибка! Некооректное значение поля: город!";

      } elseif (!preg_match("/^[а-яА-Яa-zA-Z0-9,\. ]{1,}$/u", $data["address"])) {
        $msg = "Ошибка! Некорректное значение поля: адрес!";

      } elseif (!preg_match("/^[0-9]{5,6}$/", $data["zip_code"])) {
        $msg = "Ошибка! Некооретное значение поля: почтовый индекс!";

      }
    }

    return $msg;
  }

  public static function bookInformation($data) {
    $msg = "";

    if (empty($data["title"]) || empty($data["author"]) || empty($data["language"]) ||
        empty($data["keywords"]) || empty($data["series"]) || empty($data["rightholder"]) ||
        empty($data["age_restrictions"]) || empty($data["isbn"]) || empty($data["price"]) ||
        empty($data["count"]) || empty($data["annotation"])) {
      $msg = "Ошибка! Не все поля заполнены!";

    } elseif (!preg_match("/^[а-яА-Яa-zA-Z0-9\.,\- ]{1,}$/u", $data["title"])) {
      $msg = "Ошибка! Некорректное значение поля: название!";

    } elseif (!preg_match("/^[а-яА-Яa-zA-Z\.\-\(\), ]{1,}$/u", $data["author"])) {
      $msg = "Ошибка! Некорректное значение поля: автор!";

    } elseif (!preg_match("/^[а-яА-Яa-zA-Z]{1,}$/u", $data["language"])) {
      $msg = "Ошибка! Некоректное значение поля: язык книги!";

    } elseif (!preg_match("/^[а-яА-Яa-zA-Z, ]{1,}$/u", $data["keywords"])) {
      $msg = "Ошибка! Некорректное значение поля: ключевые слова!";

    } elseif (!preg_match("/^[а-яА-Яa-zA-Z\.,#\- ]{1,}$/u", $data["series"])) {
      $msg = "Ошибка! Некорректное значение поля: серии!";

    } elseif (!preg_match("/^[а-яА-Яa-zA-Z\-\«\»\",. ]{1,}$/u", $data["rightholder"])) {
      $msg = "Ошибка! Некорректное значение поля: правообладатель!";

    } elseif (!preg_match("/^([0-9]|\+){1,}$/u", $data["age_restrictions"])) {
      $msg = "Ошибка! Некорректное значение поля: возрастные ограничения!";

    } elseif (!preg_match("/^978\-[0-9]\-[0-9]{5}\-[0-9]{3}\-[0-9]$/u", $data["isbn"])) {
      $msg = "Ошибка! Некорректное значение поля: ISBN!";

    } elseif (!preg_match("/^[0-9]{1,}$/u", $data["price"])) {
      $msg = "Ошибка! Некорректная цена товара!";

    } elseif (!preg_match("/^[0-9]{1,}$/u", $data["count"])) {
      $msg = "Ошибка! Некорректное количество товаров!";

    } /*elseif (!preg_match("/^[а-яА-Яa-zA-Z\-\«\»\"!\(\) ]{1,}$/u", $data["annotation"])) {
      $msg = "Ошибка! Аннотация содержит недопустимые символы!";

    }*/

    return $msg;
  }

  public static function infoBlock($data) {
    $msg = "";

    if (empty($data["title"]) || empty($data["access_level"]) && $data["access_level"] != 0 || empty($data["content"])) {
      $msg = "Ошибка! Не все поля заполнены!";

    } elseif (!preg_match("/^[а-яА-Яa-zA-Z ]{1,}$/u", $data["title"])) {
      $msg = "Ошибка! Некорректное значение поля: заголовок!";

    } elseif (!preg_match("/^[0-9]{1,}$/u", $data["access_level"])) {
      $msg = "Ошибка! Некорректное значение поля: уровень доступа!";

    }

    return $msg;
  }

  public static function review($data) {
    $msg = "";

    if (!isset($data["rating"]) && empty($data["text"])) {
      $msg = "Ошибка! Не все поля заполнены!";

    } elseif (!preg_match("/^[0-5]$/", $data["rating"])) {
      $msg = "Ошибка! Некорректное значение рейтинга!\nПопробуйте обновить страницу! И написать отзыв по новой!";

    } elseif (!preg_match("/^[а-яА-Яa-zA-Z0-9\.\!\?, ]{1,}$/u", $data["text"])) {
      $msg = "Ошибка! Некорректное значение поля: текст отзыва!";

    }

    return $msg;
  }

  public static function order($data) {
    $msg = "";

    if (!isset($data["dilivery_method"]) && !isset($data["payment_method"]) &&
        !isset($data["date_of_dilivery"]) && !isset($data["time_of_dilivery"])) {
      $msg = "Ошибка! Переданы не все поля! Попробуйте обновить страницу и повторить процедуру!";

    } elseif (!preg_match("/^[1-3]$/", $data["dilivery_method"])) {
      $msg = "Ошибка! Некорректный формат доставки!";

    } elseif (!preg_match("/^[1-3]$/", $data["payment_method"])) {
      $msg = "Ошибка! Некорректный формат оплаты!";

    } elseif ($data["payment_system"] != null && !preg_match("/^[1-4]$/", $data["payment_system"])) {
      $msg = "Ошибка! Некорректная платежная система!";

    } elseif (!preg_match("/^[1-7]$/", $data["date_of_dilivery"])) {
      $msg = "Ошибка! Некорректная дата доставки!";

    } elseif (!preg_match("/^[1-4]$/", $data["time_of_dilivery"])) {
      $msg = "Ошибка! Некорректное время доставки!";

    }

    return $msg;
  }
}

?>
