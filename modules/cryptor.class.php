<?php

class Cryptor {

  public static function encryptText($text) {
    return self::getHash($text, mb_substr(uniqid(), 0, 10, "utf-8"));
  }

  public static function getHash($text, $salt) {
    return $salt . md5($salt . $text);
  }

  public static function confirmPasswords($pass, $hash) {
    $salt = mb_substr($hash, 0, 10, "utf-8");
    if (self::getHash($pass, $salt) == $hash) return true;

    return false;
  }

}

 ?>
