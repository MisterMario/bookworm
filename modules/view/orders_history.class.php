<?php

namespace PageBuilder;

require_once("order_info.class.php");

use DB;


class OrdersHistory {
  public static function getListHTML($user_id) {
    $orders_list = "";

    $db = DB::getInstance();
    $res = $db->query("SELECT * FROM ".DB_TABLES["order"]." WHERE user_id=".$user_id." ORDER BY id DESC");
    if (gettype($res) == "boolean" || $res->num_rows == 0) return EmptyContent::getHTML(3);

    while ($row = $res->fetch_assoc()) {
      $canBeEdited = false; $canCancel = false;

      $order = array("id" => $row["id"],
                     "status" => $row["status"],
                     "num_books" => $row["books_of_count"],
                     "total_price" => $row["total_price"],
                     "date_of_issue" => OrderInfo::getFormatedDate($row["date_of_issue"]),
                     "date_of_dilivery" => OrderInfo::getFormatedDate($row["date_of_dilivery"]),
                     "dilivery_time" => OrderInfo::getDiliveryTimeName($row["time_of_dilivery"]),
                     "dilivery_method" => OrderInfo::getDiliveryMethodName($row["dilivery_method"]),
                     "payment_method" => OrderInfo::getPaymentMethodName($row["payment_method"]),
                     "status_name" => OrderInfo::getStatusName($row["status"]));

      if ((int)$order["status"] < ORDER_STATUS[2]) { // Если заказ еще не в пути, то его можно изменить или отменить.
        //$canBeEdited = true;
        $canCancel = true;
      }

      ob_start(); include SERVER_VIEW_DIR."order.html";
      $orders_list .= ob_get_clean();
    }

    ob_start(); include SERVER_VIEW_DIR."purchase_history.html";
    return ob_get_clean();
  }
}

 ?>
