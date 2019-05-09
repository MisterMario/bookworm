<?php

namespace PageBuilder;

use DB;


class Review {
  public static function getReviewsList($book_id) {
    global $user;

    $list = "";
    $db = DB::getInstance();

    $res = $db->query("SELECT * FROM ".DB_TABLES["review"]." WHERE book_id=".$book_id." ORDER BY id DESC");
    if (gettype($res) == "boolean" || $res->num_rows == 0) return null;

    while ($row = $res->fetch_assoc()) {
      $review = array("id" => $row["id"], "text" => $row["text"], "user" => "", "rating" => "");
      $review["text_rows"] = ceil(mb_strlen($row["text"], "utf-8") / 87);

      $username = $db->query("SELECT firstname, lastname FROM ".DB_TABLES["user"]." WHERE id=".$row["user_id"]." LIMIT 1"); // Получение имени и фамилии автора комментария
      if (gettype($username) == "boolean" || $username->num_rows == 0) $review["user"] = "Автор<br />Неизвестен";
      else {
        $username = $username->fetch_assoc();
        $review["user"] = $username["firstname"]."<br />".$username["lastname"];
      }

      for ($i = 1; $i <= 5; $i++) { // Формирование рейтинга отзыва
        if ($i <= (int)$row["rating"]) $review["rating"] .= "<li class=\"active\" value=\"".$i."\"></li>";
        else $review["rating"] .= "<li class=\"\" value=\"".$i."\"></li>";
      }

      // Определение может ли пользователь управлять отзывом
      $isOwner = $user && $user->getId() == $row["user_id"] ? true : false; // Автор отзыва
      $isMember = $user && $user->getLevel() > 2 ? true : false; // Админ

      ob_start(); include SERVER_VIEW_DIR."review.html";
      $list .= ob_get_clean();
    }
    return $list;
  }
}

 ?>
