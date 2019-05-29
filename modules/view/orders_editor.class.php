<?php

namespace PageBuilder;

require_once("book.class.php");
require_once("order_info.class.php");

use DB;


class OrdersEditor {
  public static function getEditHTML($id) {
    $db = DB::getInstance();
    $isEdited = true;

    $order_selection = $db->query("SELECT * FROM ".DB_TABLES["order"]." WHERE id='".$id."' LIMIT 1");
    if (!DB::checkDBResult($order_selection)) return null;
    $order = $order_selection->fetch_assoc();

    $order["date_of_issue"] = OrderInfo::getFormatedDate($order["date_of_issue"]);
    $order["dilivery"] = OrderInfo::getFormatedDate($order["date_of_dilivery"])." в ".OrderInfo::getDiliveryTimeName($order["time_of_dilivery"]);
    $order["dilivery_method"] = OrderInfo::getDiliveryMethodName($order["dilivery_method"]);
    $order["payment_method"] = OrderInfo::getPaymentMethodName($order["payment_method"]);
    $order["status_name"] = OrderInfo::getStatusName($order["status"]);

    // Если ИД пользователя == 1, то это неавторизованный пользователь и информация о нем лежит в таблице "customer"
    $query_text = "";
    if ($order["user_id"] != 1)
      $query_text = "SELECT * FROM ".DB_TABLES["user"]." WHERE id='${order["user_id"]}' LIMIT 1";
    else
      $query_text = "SELECT * FROM ".DB_TABLES["customer"]." WHERE order_id='${order["id"]}' LIMIT 1";

    $customer_selection = $db->query($query_text);
    if (!DB::checkDBResult($customer_selection)) return null;
    $customer = $customer_selection->fetch_assoc();

    $customer["name"] = $customer["firstname"]." ".$customer["lastname"];
    if (isset($customer["gender"]))
      $customer["gender_name"] =  $customer["gender"] == "1" ? "мужской" : "женский";
    else
      $customer["gender_name"] = "не указан";
    $customer["status"] = $order["user_id"] != 1 ? "зарегистрирован" : "не зарегистрирован";

    /*ob_start();
    foreach ($customer as $key => $value) {
      echo "[${key}]: ${value}<br />";
    }
    echo ob_get_clean();*/

    $book_list_from_order_selection = $db->query("SELECT book_io.*, book.name, book.author, book.price ".
                                                 "FROM ".DB_TABLES["book_io"]." AS book_io ".
                                                 "JOIN ".DB_TABLES["book"]." ON book.id=book_io.book_id ".
                                                 "WHERE order_id='${order["id"]}'");
    if (!DB::checkDBResult($book_list_from_order_selection)) return null;

    $book_list = "";
    //ob_start();
    while ($book = $book_list_from_order_selection->fetch_assoc()) {
      $book["id"] = $book["book_id"]; // так как ID тут взят не из таблицы "book", а из таблицы "book_in_order"
      $book["total_sum"] = (int)$book["count"] * (int)$book["price"];
      $book["image"] = Book::getImage($book["id"]);
      ob_start(); include SERVER_VIEW_DIR."book_in_order.html";
      $book_list .= ob_get_clean();
      //var_dump($book); echo "<br /><br />";
    }
    //echo ob_get_clean();
    //var_dump($book_list);

    ob_start(); include SERVER_VIEW_DIR."editor_order.html";
    return ob_get_clean();
  }

  // public static function getNewHTML() { } // Создать заказ через ПУ невозможно
}

 ?>
