<?php

namespace PageBuilder;


class MyContacts {
  public static function getHTML($user_info) { // Принимает массив с информацией о пользователе
    ob_start(); include SERVER_VIEW_DIR."my_contacts.html";

    return ob_get_clean();
  }
}

 ?>
