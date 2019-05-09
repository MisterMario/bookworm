<?php

namespace PageBuilder;


class Register {
  public static function getHTML() {
    ob_start(); include SERVER_VIEW_DIR."register.html";
    return ob_get_clean();
  }
}

 ?>
