<?php

namespace PageBuilder;

use User;


class UsersEditor {
  public static function getEditHTML($user_id) {
    $isEdit = true;
    $user_info = User::getUserInformation($user_id);

    ob_start(); include SERVER_VIEW_DIR."editor_user.html";
    return ob_get_clean();
  }

  public static function getNewHTML() {
    $isEdit = false;
    $user_info = array("id" => 0,
                       "firstname" => "",
                       "lastname" => "",
                       "email" => "",
                       "gender" => 1,
                       "phone_number" => "",
                       "level" => 1,
                       "state" => "",
                       "city" => "",
                       "address" => "",
                       "zip_code" => "");

    ob_start(); include SERVER_VIEW_DIR."editor_user.html";
    return ob_get_clean();
  }
}

 ?>
