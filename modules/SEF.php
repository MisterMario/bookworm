<?php

class SEF {
  const PATTERN_FOR_CATALOG = "/^\/catalog\/[0-9]{1,}\/{0,1}$/";
  const PATTERN_FOR_GENRE = "/^\/genre\/[0-9]{1,}(\/[0-9]{1,}){0,1}\/{0,1}$/";
  const PATTERN_FOR_BOOK = "/^\/product\/[0-9]{1,}\/{0,1}$/";
  const PATTERN_FOR_ADD_ORDER = "/^\/add-order\/{0,1}$/";
  const PATTERN_FOR_USER_REGISTRATION = "/^\/registration\/{0,1}$/";
  const PATTERN_FOR_PC_MY_CONTACTS = "/^\/pc\/my-contacts\/{0,1}$/";
  const PATTERN_FOR_PC_MY_CART = "/^\/pc\/my-cart\/{0,1}$/";
  const PATTERN_FOR_PC_ORDERS_HISTORY = "/^\/pc\/orders-history\/{0,1}$/";
  const PATTERN_FOR_ORDER = "/^\/order\/[0-9]{1,}\/{0,1}$/";
  const PATTERN_FOR_CONTROL_MENU = "/^\/control\/menu\/{0,1}$/";
  const PATTERN_FOR_CONTROL_PRODUCTS = "/^\/control\/products(\/[0-9]{1,}){0,1}\/{0,1}$/";
  const PATTERN_FOR_CONTROL_USERS = "/^\/control\/users(\/[0-9]{1,}){0,1}\/{0,1}$/";
  const PATTERN_FOR_CONTROL_INFO_BLOCKS = "/^\/control\/info-blocks(\/[0-9]{1,}){0,1}\/{0,1}$/";
  const PATTERN_FOR_CONTROL_ORDERS = "/^\/control\/orders(\/[0-9]{1,}){0,1}\/{0,1}$/";
  const PATTERN_FOR_EDIT_PRODUCT = "/^\/edit\/product\/[0-9]{1,}\/{0,1}$/";
  const PATTERN_FOR_EDIT_USER = "/^\/edit\/user\/[0-9]{1,}\/{0,1}$/";
  const PATTERN_FOR_EDIT_INFO_BLOCK = "/^\/edit\/info-block\/[0-9]{1,}\/{0,1}$/";
  const PATTERN_FOR_EDIT_ORDER = "/^\/edit\/order\/[0-9]{1,}\/{0,1}$/";
  const PATTERN_FOR_SEARCH = "/^\/search\/[a-zA-Zа-яА-Я0-9_-]{1,}$/";
  const PATTERN_FOR_CART = "/^\/cart\/{0,1}$/"; // Корзина для неавторизованного пользователя

  public static function getPageInfo($uri) {
    $uri = strtok($uri, "?");
    $pi = null;

    if ($uri == "/" || preg_match(self::PATTERN_FOR_CATALOG, $uri)) {

      preg_match_all("/[0-9]{1,}/", $uri, $numbers);
      $pi = array("page_code" => 1, "item_code" => 0, "page_num" => count($numbers[0]) == 1 ? (int)$numbers[0][0] : 1);

    } elseif (preg_match(self::PATTERN_FOR_GENRE, $uri)) {

      preg_match_all("/[0-9]{1,}/", $uri, $numbers);
      $pi = array("page_code" => 2, "item_code" => (int)$numbers[0][0], "page_num" => count($numbers[0]) == 2 ? (int)$numbers[0][1] : 1); // Если страница не указана, то берется первая

    } elseif (preg_match(self::PATTERN_FOR_BOOK, $uri)) {

      preg_match_all("/[0-9]{1,}/", $uri, $numbers);
      $pi = array("page_code" => 3, "item_code" => (int)$numbers[0][0], "page_num" => 0);

    } elseif (preg_match(self::PATTERN_FOR_ADD_ORDER, $uri)) {

      $pi = array("page_code" => 4, "item_code" => 0, "page_num" => 0);

    } elseif (preg_match(self::PATTERN_FOR_USER_REGISTRATION, $uri)) {

      $pi = array("page_code" => 5, "item_code" => 0, "page_num" => 0);

    } elseif (preg_match(self::PATTERN_FOR_PC_MY_CONTACTS, $uri)) {

      $pi = array("page_code" => 6, "item_code" => 1, "page_num" => 0);

    } elseif (preg_match(self::PATTERN_FOR_PC_MY_CART, $uri)) {

      $pi = array("page_code" => 6, "item_code" => 2, "page_num" => 0);

    } elseif (preg_match(self::PATTERN_FOR_PC_ORDERS_HISTORY, $uri)) {

      $pi = array("page_code" => 6, "item_code" => 3, "page_num" => 0);

    } elseif (preg_match(self::PATTERN_FOR_ORDER, $uri)) {

      preg_match_all("/[0-9]{1,}/", $uri, $numbers);
      $pi = array("page_code" => 7, "item_code" => (int)$numbers[0][0], "page_num" => 0);

    } elseif (preg_match(self::PATTERN_FOR_CONTROL_PRODUCTS, $uri)) {

      preg_match_all("/[0-9]{1,}/", $uri, $numbers);
      $pi = array("page_code" => 8, "item_code" => 1, "page_num" => count($numbers[0]) != 0 ? (int)$numbers[0][0] : 1);

    } elseif (preg_match(self::PATTERN_FOR_CONTROL_USERS, $uri)) {

      preg_match_all("/[0-9]{1,}/", $uri, $numbers);
      $pi = array("page_code" => 8, "item_code" => 2, "page_num" => count($numbers[0]) != 0 ? (int)$numbers[0][0] : 1);

    } elseif (preg_match(self::PATTERN_FOR_CONTROL_INFO_BLOCKS, $uri)) {

      preg_match_all("/[0-9]{1,}/", $uri, $numbers);
      $pi = array("page_code" => 8, "item_code" => 3, "page_num" => count($numbers[0]) != 0 ? (int)$numbers[0][0] : 1);

    } elseif (preg_match(self::PATTERN_FOR_CONTROL_MENU, $uri)) {

      $pi = array("page_code" => 8, "item_code" => 4, "page_num" => 0);

    } elseif (preg_match(self::PATTERN_FOR_CONTROL_ORDERS, $uri)) {

      $pi = array("page_code" => 8, "item_code" => 5, "page_num" => 0);

    } elseif (preg_match(self::PATTERN_FOR_EDIT_PRODUCT, $uri)) {

      preg_match_all("/[0-9]{1,}/", $uri, $numbers);
      $pi = array("page_code" => 9, "item_code" => 1, "page_num" => (int)$numbers[0][0]);

    } elseif (preg_match(self::PATTERN_FOR_EDIT_USER, $uri)) {

      preg_match_all("/[0-9]{1,}/", $uri, $numbers);
      $pi = array("page_code" => 9, "item_code" => 2, "page_num" => (int)$numbers[0][0]);

    } elseif (preg_match(self::PATTERN_FOR_EDIT_INFO_BLOCK, $uri)) {

      preg_match_all("/[0-9]{1,}/", $uri, $numbers);
      $pi = array("page_code" => 9, "item_code" => 3, "page_num" => (int)$numbers[0][0]);

    } elseif (preg_match(self::PATTERN_FOR_EDIT_ORDER, $uri)) {

      preg_match_all("/[0-9]{1,}/", $uri, $numbers);
      $pi = array("page_code" => 9, "item_code" => 4, "page_num" => (int)$numbers[0][0]);

    } elseif (preg_match(self::PATTERN_FOR_SEARCH, $uri)) { // Требует доработки

      $pi = array("page_code" => 10, "item_code" => 0, "page_num" => 1);

    } elseif (preg_match(self::PATTERN_FOR_CART, $uri)) { // Корзина для неавторизованного пользователя

      $pi = array("page_code" => 11, "item_code" => 0, "page_num" => 0);

    }
    return $pi;
  }
}

?>