<?php

namespace PageBuilder;

require_once("book.class.php");
require_once("order_info.class.php");

use DB;


class ControlPanel {

  public static function getMenuHTML() {
    ob_start(); include SERVER_VIEW_DIR."control-panel.html";
    return ob_get_clean();
  }

  public static function getBooksListHTML($page_num, $count) {
    $db = DB::getInstance();
    $list = ""; $pages_navigation = "";

    $offset = ($page_num * $count) - $count;
    $res = $db->query("SELECT id, name, author, price, count FROM ".DB_TABLES["book"]." ORDER BY id LIMIT ".$count." OFFSET ".$offset);

    if (DB::checkDBResult($res)) {

      while ($row = $res->fetch_assoc()) {
        $book = array("id" => $row["id"], "name" => $row["name"], "author" => $row["author"], "price" => $row["price"], "count" => $row["count"]);
        $book["total_sum"] = (int)$book["price"] * (int)$book["count"];
        $book["image"] = Book::getImage($book["id"]);
        ob_start(); include SERVER_VIEW_DIR."cp_small_book.html";
        $list .= ob_get_clean();
      }
      $num_pages = (int)( $db->query("SELECT count(id) FROM ".DB_TABLES["book"])->fetch_assoc()["count(id)"] ) / $count;
      $num_pages = (int)ceil($num_pages);
      $pages_navigation = Page::getPageNavigation($num_pages, $page_num, "/control/products/");

    } else $list = self::getErrorMessage(6);

    ob_start(); include SERVER_VIEW_DIR."cp_books.html";
    return ob_get_clean();
  }

  public static function getUsersListHTML($page_num, $count) {
    $db = DB::getInstance();
    $list = ""; $pages_navigation = "";

    $offset = ($page_num * $count) - $count;
    $res = $db->query("SELECT id, firstname, lastname, email FROM ".DB_TABLES["user"]." ORDER BY id LIMIT ".$count." OFFSET ".$offset);

    if (DB::checkDBResult($res)) {

      while ($row = $res->fetch_assoc()) {
        $user_info = array("id" => $row["id"], "firstname" => $row["firstname"], "lastname" => $row["lastname"], "email" => $row["email"]);
        ob_start(); include SERVER_VIEW_DIR."cp_small_user.html";
        $list .= ob_get_clean();
      }
      $num_pages = (int)( $db->query("SELECT count(id) FROM ".DB_TABLES["user"])->fetch_assoc()["count(id)"] ) / $count;
      $num_pages = (int)ceil($num_pages);
      $pages_navigation = Page::getPageNavigation($num_pages, $page_num, "/control/users/");

    } else $list = self::getErrorMessage(7);

    ob_start(); include SERVER_VIEW_DIR."cp_users.html";
    return ob_get_clean();
  }

  public static function getInfoBlocksListHTML($page_num, $count) {
    $db = DB::getInstance();
    $list = ""; $pages_navigation = "";

    $offset = ($page_num * $count) - $count;
    $res = $db->query("SELECT id, title, access_level FROM ".DB_TABLES["i-block"]." ORDER BY id LIMIT ".$count." OFFSET ".$offset);
    if (DB::checkDBResult($res)) {

      while ($row = $res->fetch_assoc()) {
        $ib = array("id" => $row["id"], "title" => $row["title"], "access_level" => $row["access_level"]);
        ob_start(); include SERVER_VIEW_DIR."cp_small_info_block.html";
        $list .= ob_get_clean();
      }
      $num_pages = (int)( $db->query("SELECT count(id) FROM ".DB_TABLES["i-block"])->fetch_assoc()["count(id)"] ) / $count;
      $num_pages = (int)ceil($num_pages);
      $pages_navigation = Page::getPageNavigation($num_pages, $page_num, "/control/info-blocks/");

    } else $list = self::getErrorMessage(8);

    ob_start(); include SERVER_VIEW_DIR."cp_info_blocks.html";
    return ob_get_clean();
  }

  public static function getGenresListHTML($page_num, $count) {
    $db = DB::getInstance();
    $list = ""; $pages_navigation = "";

    $offset = ($page_num * $count) - $count;
    $res = $db->query("SELECT id, name FROM ".DB_TABLES["genre"]." ORDER BY id LIMIT ".$count." OFFSET ".$offset);

    if (DB::checkDBResult($res)) {

      while ($genre = $res->fetch_assoc()) {
        ob_start(); include SERVER_VIEW_DIR."cp_small_genre.html";
        $list .= ob_get_clean();
      }
      $num_pages = (int)( $db->query("SELECT count(id) FROM ".DB_TABLES["genre"])->fetch_assoc()["count(id)"] ) / $count;
      $num_pages = (int)ceil($num_pages);
      $pages_navigation = Page::getPageNavigation($num_pages, $page_num, "/control/genres/");

    } else $list = self::getErrorMessage(5);

    ob_start(); include SERVER_VIEW_DIR."cp_genres.html";
    return ob_get_clean();
  }

  public static function getOrdersListHTML($page_num, $count) {
    $db = DB::getInstance();
    $list = ""; $pages_navigation = "";
    $user_orders = array(); $customer_orders = array();
    $orders_list = array();

    $offset = ($page_num * $count) - $count;
    $orders_selection = $db->query("SELECT * FROM ".DB_TABLES["order"]." ORDER BY id LIMIT ".$count." OFFSET ".$offset);

    $i = 0;
    while ($order = $orders_selection->fetch_assoc()) {
      $order["date_of_issue"] = OrderInfo::getFormatedDate($order["date_of_issue"]);
      $order["status_name"] = OrderInfo::getStatusName($order["status"]);
      if ($order["user_id"] != 1)
        $user_orders[] += $order["user_id"];
      else
        $customer_orders[] += $order["id"];
      $orders_list[$i] = $order;
      $i++;
    }

    if (count($user_orders) != 0) {
      $selection_user_orders = $db->query("SELECT id, firstname, lastname, email FROM ".DB_TABLES["user"].
                                          " WHERE id IN (".implode(", ", $user_orders).")");
      while ($user = $selection_user_orders->fetch_assoc()) {
        for ($i=0; $i < count($orders_list); $i++) {
          if ($orders_list[$i]["user_id"] == $user["id"])
            foreach ($user as $key => $value)
              if ($key != "id")
                $orders_list[$i][$key] = $value;
        }
      }
    }

    if (count($customer_orders) != 0) {
      $selection_customer_orders = $db->query("SELECT id, order_id, firstname, lastname, email FROM ".DB_TABLES["customer"].
                                              " WHERE order_id IN (".implode(", ", $customer_orders).")");
      while ($customer = $selection_customer_orders->fetch_assoc()) {
        $index = 0;
        for ($i=0; $i < count($orders_list); $i++) {
          if ($orders_list[$i]["id"] == $customer["order_id"]) {
            $index = $i; break;
          }
        }
        foreach ($customer as $key => $value)
          if ($key != "id" && $key != "order_id")
            $orders_list[$index][$key] = $value;
      }
    }

    if (DB::checkDBResult($orders_selection)) {

      for ($i=0; $i < count($orders_list); $i++) {
        $order = $orders_list[$i];
        $order["name"] = $order["firstname"]." ".$order["lastname"];
        ob_start(); include SERVER_VIEW_DIR."cp_small_order.html";
        $list .= ob_get_clean();
      }
      $num_pages = (int)( $db->query("SELECT count(id) FROM ".DB_TABLES["order"])->fetch_assoc()["count(id)"] ) / $count;
      $num_pages = (int)ceil($num_pages);
      $pages_navigation = Page::getPageNavigation($num_pages, $page_num, "/control/orders/");

    } else $list = self::getErrorMessage(10);

    $status = 0;
    ob_start(); include SERVER_VIEW_DIR."cp_orders.html";
    return ob_get_clean();
  }

  public static function getOrderListByStatus($status, $page_num, $count) {
    $db = DB::getInstance();
    $list = ""; $pages_navigation = "";
    $user_id_variety = ""; $customer_order_id_variety = ""; // Для того, чтобы сделать все в 2 запроса, вместо множества
    $order_list = array();

    $offset = ($page_num * $count) - $count;
    $order_selection = $db->query("SELECT id, total_price, status, date_of_issue, user_id ".
                                   "FROM ".DB_TABLES["order"]." WHERE status='${status}' ".
                                   "ORDER BY id LIMIT ${count} OFFSET ${offset}");

    if (DB::checkDBResult($order_selection)) {

      $i = 0;
      while ($order = $order_selection->fetch_assoc()) {
        $order_list[$i] = $order;

        if ($order["user_id"] != 1)
          $user_id_variety .= $order["user_id"].", ";
        else
          $customer_order_id_variety .= $order["id"].", ";

        $i++;
      }

      if (strlen($user_id_variety) > 0) {

        $user_id_variety = "(".substr($user_id_variety, 0, strlen($user_id_variety)-2).")"; // удаление последних ", "
        $user_selection = $db->query("SELECT id, firstname, lastname, email FROM ".DB_TABLES["user"].
                                     " WHERE id IN ${user_id_variety} ORDER BY id");

        if (DB::checkDBResult($user_selection)) {
          while ($user = $user_selection->fetch_assoc()) {
            for ($i=0; $i < count($order_list); $i++)
              if ($order_list[$i]["user_id"] == $user["id"]) {
                $order_list[$i]["name"] = $user["firstname"]." ".$user["lastname"];
                $order_list[$i]["email"] = $user["email"];
                break;
              }
          }
        }
      }

      if (strlen($customer_order_id_variety) > 0) {

        $customer_order_id_variety = "(".substr($customer_order_id_variety, 0, strlen($customer_order_id_variety)-2).")"; // удаление последних ", "
        $customer_selection = $db->query("SELECT order_id, firstname, lastname, email FROM ".DB_TABLES["customer"].
                                         " WHERE order_id IN ${customer_order_id_variety} ORDER BY id");

        if (DB::checkDBResult($customer_selection)) {
          while ($customer = $customer_selection->fetch_assoc()) {
            for ($i=0; $i < count($order_list); $i++)
              if ($order_list[$i]["id"] == $customer["order_id"]) {
                $order_list[$i]["name"] = $customer["firstname"]." ".$customer["lastname"];
                $order_list[$i]["email"] = $customer["email"];
                break;
              }
          }
        }
      }

    foreach ($order_list as $order) {
      $order["status_name"] = OrderInfo::getStatusName($order["status"]);
      $order["date_of_issue"] = OrderInfo::getFormatedDate($order["date_of_issue"]);
      ob_start(); include SERVER_VIEW_DIR."cp_small_order.html";
      $list .= ob_get_clean();
    }

    $num_pages = (int)( $db->query("SELECT count(id) FROM ".DB_TABLES["order"])->fetch_assoc()["count(id)"] ) / $count;
    $num_pages = (int)ceil($num_pages);
    $pages_navigation = Page::getPageNavigation($num_pages, $page_num, "/control/orders/");

  } else $list = self::getErrorMessage(11, OrderInfo::getStatusName($status));

    ob_start(); include SERVER_VIEW_DIR."cp_orders.html";
    return ob_get_clean();
  }

  // $count - число пользователей, отображаемых на одной странице
  public static function getSearchResultsByUsers($searched_string, $page_num, $count) {
    $db = DB::getInstance();
    $list = ""; $pages_navigation = "";

    $offset = ($page_num * $count) - $count;
    $selection = $db->query("SELECT id, firstname, lastname, email FROM ".DB_TABLES["user"]." WHERE ".
                            "firstname LIKE '%${searched_string}%' OR ".
                            "lastname LIKE '%${searched_string}%' OR ".
                            "email LIKE '%${searched_string}%' ".
                            "ORDER BY id LIMIT ${count} OFFSET ${offset}");

    if (DB::checkDBResult($selection)) {

      while ($row = $selection->fetch_assoc()) {
        $user_info = array("id" => $row["id"], "firstname" => $row["firstname"],
                           "lastname" => $row["lastname"], "email" => $row["email"]);
        ob_start(); include SERVER_VIEW_DIR."cp_small_user.html";
        $list .= ob_get_clean();
      }

      $num_pages = (int)( $db->query("SELECT count(id) FROM ".DB_TABLES["user"]." WHERE ".
                                     "firstname LIKE '%${searched_string}%' OR ".
                                     "lastname LIKE '%${searched_string}%' OR ".
                                     "email LIKE '%${searched_string}%'")->fetch_assoc()["count(id)"] ) / $count;
      $num_pages = (int)ceil($num_pages);
      $pages_navigation = Page::getPageNavigation($num_pages, $page_num, "/control/users/search/${searched_string}/");

    } else $list = self::getErrorMessage(3, $searched_string);

    ob_start(); include SERVER_VIEW_DIR."cp_users.html";
    return ob_get_clean();
  }

  public static function getSearchResultsByProducts($searched_string, $page_num, $count) {
    $db = DB::getInstance();
    $list = ""; $pages_navigation = "";

    $offset = ($page_num * $count) - $count;
    $selection = $db->query("SELECT id, name, author, price, count FROM ".DB_TABLES["book"]." WHERE ".
                      "name LIKE '%${searched_string}%' OR ".
                      "author LIKE '%${searched_string}%' ".
                      "ORDER BY id LIMIT ${count} OFFSET ${offset}");

    if (DB::checkDBResult($selection)) {

      while ($row = $selection->fetch_assoc()) {
        $book = array("id" => $row["id"], "name" => $row["name"], "author" => $row["author"],
                      "price" => $row["price"], "count" => $row["count"],
                      "total_sum" => (int)$row["price"] * (int)$row["count"],
                      "image" => Book::getImage($row["id"]));
        ob_start(); include SERVER_VIEW_DIR."cp_small_book.html";
        $list .= ob_get_clean();
      }

      $num_pages = (int)( $db->query("SELECT count(id) FROM ".DB_TABLES["book"]." WHERE ".
                                     "name LIKE '%${searched_string}%' OR ".
                                     "author LIKE '%${searched_string}%' ")->fetch_assoc()["count(id)"] ) / $count;
      $num_pages = (int)ceil($num_pages);
      $pages_navigation = Page::getPageNavigation($num_pages, $page_num, "/control/products/search/${searched_string}/");

    } else $list = $list = self::getErrorMessage(2, $searched_string);

    ob_start(); include SERVER_VIEW_DIR."cp_books.html";
    return ob_get_clean();
  }

  public static function getSearchResultsByInfoBlocks($searched_string, $page_num, $count) {
    $db = DB::getInstance();
    $list = ""; $pages_navigation = "";

    $offset = ($page_num * $count) - $count;
    $selection = $db->query("SELECT id, title, access_level FROM ".DB_TABLES["i-block"]." WHERE ".
                            "title LIKE '%${searched_string}%' ".
                            "ORDER BY id LIMIT ".$count." OFFSET ".$offset);

    if (DB::checkDBResult($selection)) {

      while ($row = $selection->fetch_assoc()) {
        $ib = array("id" => $row["id"], "title" => $row["title"], "access_level" => $row["access_level"]);
        ob_start(); include SERVER_VIEW_DIR."cp_small_info_block.html";
        $list .= ob_get_clean();
      }

      $num_pages = (int)( $db->query("SELECT count(id) FROM ".DB_TABLES["i-block"]." ".
                                     "WHERE title LIKE '%${searched_string}%' ")->fetch_assoc()["count(id)"] ) / $count;
      $num_pages = (int)ceil($num_pages);
      $pages_navigation = Page::getPageNavigation($num_pages, $page_num, "/control/info-blocks/search/${searched_string}/");

    } else $list = self::getErrorMessage(4, $searched_string);

    ob_start(); include SERVER_VIEW_DIR."cp_info_blocks.html";
    return ob_get_clean();
  }

  public static function getSearchResultsByGenres($searched_string, $page_num, $count) {
    $db = DB::getInstance();
    $list = ""; $pages_navigation = "";

    $offset = ($page_num * $count) - $count;
    $selection = $db->query("SELECT id, name FROM ".DB_TABLES["genre"]." WHERE ".
                            "name LIKE '%${searched_string}%' ".
                            "ORDER BY id LIMIT ".$count." OFFSET ".$offset);
    if (DB::checkDBResult($selection)) {

      while ($genre = $selection->fetch_assoc()) {
        ob_start(); include SERVER_VIEW_DIR."cp_small_genre.html";
        $list .= ob_get_clean();
      }

      $num_pages = (int)( $db->query("SELECT count(id) FROM ".DB_TABLES["genre"]." ".
                                     "WHERE name LIKE '%${searched_string}%' ")->fetch_assoc()["count(id)"] ) / $count;
      $num_pages = (int)ceil($num_pages);
      $pages_navigation = Page::getPageNavigation($num_pages, $page_num, "/control/genres/search/${searched_string}/");

    } else $list = self::getErrorMessage(1, $searched_string);

    ob_start(); include SERVER_VIEW_DIR."cp_genres.html";
    return ob_get_clean();
  }

  public static function getSearchResultsByOrders($searched_string, $page_num, $count) {
    $db = DB::getInstance();
    $list = ""; $pages_navigation = "";
    $user_id_variety = "";
    $customer_id_variety = "";
    $order_list = array();

    $offset = ($page_num * $count) - $count;
    $user_id_selection = $db->query("SELECT id FROM ".DB_TABLES["user"]." WHERE ".
                                    "(firstname LIKE '%${searched_string}%' OR ".
                                    "lastname LIKE '%${searched_string}%' OR ".
                                    "email LIKE '%${searched_string}%') AND ".
                                    "id NOT LIKE '1' ". // нельзя извлекать системного пользователя (его ID: 1)
                                    "ORDER BY id LIMIT ".$count." OFFSET ".$offset);
    $customer_id_selection = $db->query("SELECT id FROM ".DB_TABLES["customer"]." WHERE ".
                                              "firstname LIKE '%${searched_string}%' OR ".
                                              "lastname LIKE '%${searched_string}%' OR ".
                                              "email LIKE '%${searched_string}%' ".
                                              "ORDER BY id LIMIT ".$count." OFFSET ".$offset);

    if (DB::checkDBResult($user_id_selection)) {
      while ($user = $user_id_selection->fetch_assoc()) {
        $user_id_variety .= $user["id"].", ";
      }
      $user_id_variety = substr($user_id_variety, 0, strlen($user_id_variety)-2); // обрезка лишнего ", "
      $user_id_variety = "(${user_id_variety})";
    }
    if (DB::checkDBResult($customer_id_selection)) {
      while ($customer = $customer_id_selection->fetch_assoc()) {
        $customer_id_variety .= $customer["id"].", ";
      }
      $customer_id_variety = substr($customer_id_variety, 0, strlen($customer_id_variety)-2);
      $customer_id_variety = "(${customer_id_variety})";
    }

    if (strlen($user_id_variety) != 0 || strlen($customer_id_variety) != 0) {

      if (strlen($user_id_variety) != 0) {
        $user_order_selection = $db->query("SELECT o.id, o.total_price, o.books_of_count, o.status, o.date_of_issue, ".
                                           "u.firstname, u.lastname ".
                                           "FROM ".DB_TABLES["order"]." AS o ".
                                           "JOIN ".DB_TABLES["user"]." AS u ON o.user_id=u.id ".
                                           "WHERE o.user_id IN ${user_id_variety}");

        $i = 0;
        while ($order = $user_order_selection->fetch_assoc()) {
          $order_list[$i] = $order;
          $i++;
        }
      }

      if (strlen($customer_id_variety) != 0) {
        $customer_order_selection = $db->query("SELECT o.id, o.total_price, o.books_of_count, o.status, o.date_of_issue, ".
                                               "c.firstname, c.lastname ".
                                               "FROM ".DB_TABLES["order"]." AS o ".
                                               "JOIN ".DB_TABLES["customer"]." AS c ON o.id=c.order_id ".
                                               "WHERE c.id IN ${customer_id_variety}");
        $i = count($order_list);
        while ($order = $customer_order_selection->fetch_assoc()) {
          $order_list[$i] = $order;
          $i++;
        }
      }

      if (count($order_list) != 0) {

        // Так как слиты дле выборки - все будет не по порядку. Сортируем с использованием "Buble Sort"
        for ($i=0; $i < count($order_list); $i++) {
          for ($j=0; $j < count($order_list)-$i-1; $j++)
            if ((int)$order_list[$j]["id"] > (int)$order_list[$j + 1]["id"]) {
              $order = $order_list[$j];
              $order_list[$j] = $order_list[$j + 1];
              $order_list[$j + 1] = $order;
            }
        }

        foreach ($order_list as $order) {
          $order["name"] = $order["firstname"]." ".$order["lastname"];
          $order["status_name"] = OrderInfo::getStatusName($order["status"]);
          ob_start(); include SERVER_VIEW_DIR."cp_small_order.html";
          $list .= ob_get_clean();
        }

        $num_pages = (int)( $db->query("SELECT count(id) FROM ".DB_TABLES["genre"]." ".
                                       "WHERE name LIKE '%${searched_string}%' ")->fetch_assoc()["count(id)"] ) / $count;
        $num_pages = (int)ceil($num_pages);
        $pages_navigation = Page::getPageNavigation($num_pages, $page_num, "/control/genres/search/${searched_string}/");

      }

    }

    if (strlen($list) == 0)
      $list = self::getErrorMessage(9, $searched_string);

    $status = 0;
    ob_start(); include SERVER_VIEW_DIR."cp_orders.html";
    return ob_get_clean();
  }

  private static function getErrorMessage($code, $searched_string = "") {
    $error_message = "По запросу \"${searched_string}\" не найдено ни одного ";;
    $html_file = "";

    switch ($code) {
      case 1:
        $error_message .= "жанра!";
        $html_file = "error_no_genres.html";
        break;
      case 2:
        $error_message .= "товара!";
        $html_file = "error_no_books.html";
        break;
      case 3:
        $error_message .= "пользователя!";
        $html_file = "error_no_users.html";
        break;
      case 4:
        $error_message .= "инфо-блока!";
        $html_file = "error_no_info_blocks.html";
        break;
      case 5:
        $error_message = "Жанры отсутствуют. Для добавления воспользуйтесь кнопкой<br /> \"Добавить жанр\", расположенной выше!";
        $html_file = "error_no_genres.html";
        break;
      case 6:
        $error_message = "Нет ни одного товара.<br />Вы можете это исправить =)";
        $html_file = "error_no_books.html";
        break;
      case 7:
        $error_message = "В базе нет ни одного пользователя!<br />Но все в ваших руках!";
        $html_file = "error_no_users.html";
        break;
      case 8:
        $error_message = "Инфо-блоки отсутствуют!<br />Если желаете - добавьте их!";
        $html_file = "error_no_info_blocks.html";
        break;
      case 9:
        $error_message .= "заказа!";
        $html_file = "error_no_orders.html";
        break;
      case 10:
        $error_message = "Никто не оставлял заказов!<br />Если вам скучно - оформите парочку!";
        $html_file = "error_no_orders.html";
        break;
      case 11:
        $error_message = "Заказы со статусом <br />\"${searched_string}\"<br /> отсутствуют!";
        $html_file = "error_no_orders.html";
        break;
    }

    ob_start(); include SERVER_VIEW_DIR.$html_file;
    return ob_get_clean();
  }
}

 ?>
