<?php

namespace PageBuilder;

require_once(MODULES_DIR."cart.class.php");

use DB;
use CartControl\Cart as CCart;


class AddOrder {
  const DATES = array("Monday" => "ПН",
                      "Tuesday" => "ВТ",
                      "Wednesday" => "СР",
                      "Thursday" => "ЧТ",
                      "Friday" => "ПТ",
                      "Saturday" => "СБ",
                      "Sunday" => "ВС");

  private static function getDateList() {
    $dateList = "";
    $class_name = "active";
    $onclick = "setDiliveryDate(this);";

    for ($i = 1; $i <= 7; $i++) {
      $time = strtotime("+".$i." day");

      if ($i != 1) { // Для того, чтобы у активного и заблокированного элемента не было обработчиков.
        $class_name = "";
        $onclick = "setDiliveryDate(this);";
      }
      if (self::DATES[date("l", $time)] == "ВС") {
        $class_name = "is-impossible";
        $onclick = "";
      }

      $dateList .= "<li onclick=\"".$onclick."\" value=\"".$i."\" class=\"".$class_name."\">".self::DATES[date("l", $time)].
                   "<br /><span>".date("j", $time)."</span></li>";
    }

    return $dateList;
  }

  public static function getHTML($user) {
    $total_sum = 0;
    $isHidden = ""; // Определяет будет ли скрыт блок. Если да - содержит значение "hidden", нет - пуст.
    $dateList = self::getDateList();
    if ($user) {
      $isHidden = "hidden";
      $total_sum = CCart::getStatusForUser($user->getId())["total_sum"];
    } else $total_sum = CCart::getStatusForNoDBUser()["total_sum"];

    ob_start(); include SERVER_VIEW_DIR."add_order.html";
    return ob_get_clean();
  }
}

 ?>
