<?php

namespace PageBuilder;

require_once("book.class.php");

use DB;


class OrderInfo {
  public static function getHTML($order_id) {
    $db = DB::getInstance();
    $books_list = "";
    $isEdited = false;

    $order = self::getOrderInfo($order_id);
    if (!$order) return false;

    $books_from_order = $db->query("SELECT * FROM ".DB_TABLES["book_io"]." WHERE order_id=".$order_id);
    if (!DB::checkDBResult($books_from_order)) return false;

    while($book = $books_from_order->fetch_assoc()) {

      $book_info = $db->query("SELECT name, author, price FROM ".DB_TABLES["book"]." WHERE id=".$book["book_id"]." LIMIT 1");
      if (!DB::checkDBResult($book_info)) return null;
      $book_info = $book_info->fetch_assoc();

      $book = array("id" => $book["book_id"],
                    "name" => $book_info["name"],
                    "author" => $book_info["author"],
                    "price" => $book_info["price"],
                    "count" => $book["count"],
                    "total_sum" => (int)$book_info["price"] * (int)$book["count"],
                    "image" => Book::getImage($book["book_id"]));

      ob_start(); include SERVER_VIEW_DIR."book_in_order.html";
      $books_list .= ob_get_clean();
    }


    ob_start(); include SERVER_VIEW_DIR."information_about_order.html";
    return ob_get_clean();
  }

  public static function getEditHTML($order_id) {
    $db = DB::getInstance();
    $books_list = "";
    $isEdited = true;

    $order = self::getOrderInfo($order_id);
    if (!$order) return false;

    $books_from_order = $db->query("SELECT * FROM ".DB_TABLES["book_io"]." WHERE order_id=".$order_id);
    if (!DB::checkDBResult($books_from_order)) return false;

    while($book = $books_from_order->fetch_assoc()) {

      $book_info = $db->query("SELECT name, author, price FROM ".DB_TABLES["book"]." WHERE id=".$book["book_id"]." LIMIT 1");
      if (!DB::checkDBResult($book_info)) return null;
      $book_info = $book_info->fetch_assoc();

      $book = array("id" => $book["book_id"],
                    "name" => $book_info["name"],
                    "author" => $book_info["author"],
                    "price" => $book_info["price"],
                    "count" => $book["count"],
                    "total_sum" => (int)$book_info["price"] * (int)$book["count"],
                    "image" => Book::getImage($book["book_id"]));

      ob_start(); include SERVER_VIEW_DIR."edit_book_in_order.html";
      $books_list .= ob_get_clean();
    }

    ob_start(); include SERVER_VIEW_DIR."information_about_order.html";
    return ob_get_clean();
  }

  public static function getOrderInfo($order_id) {
    $db = DB::getInstance();

    $res = $db->query("SELECT * FROM ".DB_TABLES["order"]." WHERE id=".$order_id." LIMIT 1");
    if (!DB::checkDBResult($res)) return false;
    $res = $res->fetch_assoc();

    $order = array("id" => $res["id"],
                   "status" => $res["status"],
                   "num_books" => $res["books_of_count"],
                   "total_price" => $res["total_price"],
                   "date_of_issue" => self::getFormatedDate($res["date_of_issue"]),
                   "date_of_dilivery" => self::getFormatedDate($res["date_of_dilivery"]),
                   "dilivery_time" => self::getDiliveryTimeName($res["time_of_dilivery"]),
                   "dilivery_method" => self::getDiliveryMethodName($res["dilivery_method"]),
                   "payment_method" => self::getPaymentMethodName($res["payment_method"]),
                   "status_name" => self::getStatusName($res["status"]),
                   "canBeEdited" => false, // Редактирование заказа клиентом еще не реализовано
                   "canCancel" => false);

    if ((int)$order["status"] < ORDER_STATUS[2]) { // Если заказ еще не в пути, то его можно изменить или отменить.
      //$order["canBeEdited"] = true;
      $order["canCancel"] = true;
    }

    return $order;
  }

  public static function getDiliveryTimeName($dilivery_time) {
    switch ($dilivery_time) {
      case '1': return "10:00-12:00";
      case '2': return "12:00-14:00";
      case '3': return "14:00-16:00";
      case '4': return "16:00-18:00";
    }
    return null;
  }

  public static function getDiliveryMethodName($dilivery_method) {
    switch ($dilivery_method) {
      case '1': return "курьерская доставка";
      case '2': return "почтовая доставка";
      case '3': return "самовывоз";
    }
    return null;
  }

  public static function getPaymentMethodName($payment_method) {
    switch ($payment_method) {
      case '1': return "наличными";
      case '2': return "банковской картой";
      case '3': return "онлайн-платеж (WM)";
      case '4': return "онлайн-платеж (PayPal)";
      case '5': return "онлайн-платеж (ЯД)";
      case '6': return "онлайн-платеж (Quwi)";
    }
    return null;
  }

  public static function getStatusName($status) {
    switch ($status) {
      case '1': return "ожидает подтверждения";
      case '2': return "укомплектован";
      case '3': return "находится в пути";
      case '4': return "выполнен";
      case '5': return "отменен";
    }
    return null;
  }

  public static function getFormatedDate($date) {
    preg_match_all("/[0-9]{1,}/", $date, $numbers);
    $numbers = $numbers[0];
    return "${numbers[2]}.${numbers[1]}.${numbers[0]}";
  }
}

 ?>
