<?php

class Order {
  public static function add($order, $books) { // Принимает массивы с информцией о заказе и списком заказанных книг
    $db = DB::getInstance();
    $res = $db->query("INSERT INTO ".DB_TABLES["order"]."(user_id, books_of_count, total_price, date_of_issue,
                                                          date_of_dilivery, time_of_dilivery, dilivery_method,
                                                          payment_method)
                                                   VALUES('".$order["user_id"]."', ".
                                                         "'".$order["books_of_count"]."',".
                                                         "'".$order["total_price"]."',".
                                                         "'".$order["date_of_issue"]."',".
                                                         "'".$order["date_of_dilivery"]."',".
                                                         "'".$order["time_of_dilivery"]."',".
                                                         "'".$order["dilivery_method"]."',".
                                                         "'".$order["payment_method"]."')");

    if ($res) {
      $res = $db->query("SELECT id FROM ".DB_TABLES["order"]." ORDER BY id DESC LIMIT 1");

      if (DB::checkDBResult($res)) {
        $order["id"] = $res->fetch_assoc()["id"];

        $query = "INSERT INTO ".DB_TABLES["book_io"]."(order_id, book_id, count) VALUES";
        for ($i = 0; $i < count($books); $i++) {
          $query .= "('".$order["id"]."', '".$books[$i]["book_id"]."', '".$books[$i]["count"]."')";
          if ($i != count($books) - 1) $query .= ", ";
        }

        $res = $db->query($query);

        if (!$res) { // Если хотя бы один запрос был выполнен неудачно - производится откат всех изменений
          self::delete($order["id"]);
          // self::deleteBooksFromOrder($order["id"]); // Произойдет каскадное удаление таблиц
        }
      }
    }

    return $res;
  }

  // Можно будет дописать, чтобы можно было добавлять сразу несколько книг.
  public static function addBooksToOrder($order_id, $book) {
    $db = DB::getInstance();

    if (!self::existsOrder($order_id)) return false;

    $was_book_added = $db->query("INSERT INTO ".DB_TABLES["book_io"]."(id, order_id, book_id, count) ".
                      "VALUES('".$book["id"]."', '".$book["order_id"]."', '".$book["book_id"]."', '".$book["count"]."')");
    if (!$was_book_added) return false;

    $total_price = getTotalPriceForOrder($order_id);
    if (!$total_price) return false;

    return $db->query("UPDATE ".DB_TABLES["order"]." SET books_of_count=books_of_count+".$book["count"].", ".
                                                         "total_price='".$total_price."' WHERE id='".$order_id."'");
  }

  public static function edit($order, $books = array()) { // Можно отредактировать заказ, не затрагивая список книг
    $db = DB::getInstance();
    $res = $db->query("UPDATE ".DB_TABLES["order"].
                              " SET user_id='".$order["user_id"]."', ".
                                   "books_of_count='".$order["books_of_count"]."', ".
                                   "total_price='".$order["total_price"]."', ".
                                   "date_of_issue='".$order["date_of_issue"]."', ".
                                   "date_of_dilivery='".$order["date_of_dilivery"]."', ".
                                   "dilivery_method='".$order["dilivery_method"]."', ".
                                   "payment_method='".$order["payment_method"]."' ".
                              "WHERE id='".$order["id"]."' LIMIT 1");

    if ($res && count($books) > 0) { // Список книг может не передаваться, если редактируется только информация о заказе
      // Проще всего сначала удалить старый список книг из заказа, затем заменить его на новый.
      self::deleteBooksFromOrder($order["id"]);

      $query = "INSERT INTO ".DB_TABLES["book_ic"]."(order_id, book_id, count) VALUES";
      for ($i = 0; $i < count($books); $i++)
        $query .= "('".$order["id"].", '".$books[$i]["book_id"]."', '".$books[$i]["count"]."'), ";

      $res = $db->query($query);
    }

    if (!$res) {
      self::delete($order["id"]);
      self::deleteBooksFromOrder($order["id"]);
    }

    return $res;
  }

  public static function setCountForBookFromOrder($order_id, $book_id, $count) {
    $db = DB::getInstance();

    if (!self::existsOrder($order_id)) return false;

    return $db->query("UPDATE ".DB_TABLES["book_io"]." SET count='".$count."' ".
                      "WHERE order_id='".$order_id."' AND book_id='".$book_id."' LIMIT 1");
  }

  public static function delete($id) {
    $db = DB::getInstance();
    return $db->query("DELETE FROM ".DB_TABLES["order"]." WHERE id=".$id." LIMIT 1");
  }

  private static function deleteBooksFromOrder($id) {
    $db = DB::getInstance();
    $db->query("DELETE FROM ".DB_TABLES["book_ic"]." WHERE order_id=".$id);
  }

  public static function deleteBookFromOrder($order_id, $book_id) {
    $db = DB::getInstance();
    return $db->query("DELETE FROM ".DB_TABLES["book_io"]." ".
                      "WHERE order_id='".$order_id."' AND book_id='".$book_id."' LIMIT 1");
  }

  public static function clean() { // Полностью чистит таблицу. Функция для администратора
    $db = DB::getInstance();
    return $db->query("DELETE FROM ".DB_TABLES["order"]);
  }

  public static function changeStatus($order_id, $status) {
    $db = DB::getInstance();
    return $db->query("UPDATE ".DB_TABLES["order"]." SET status='".$status."' WHERE id='".$order_id."' LIMIT 1");
  }

  public static function getTotalPriceForOrder($order_id) {
    $db = DB::getInstance();
    $count = 0;

    if (!self::existsOrder($order_id)) return false;
    $books_list = $db->query("SELECT book_id, count FROM ".DB_TABLES["book_io"]." WHERE order_id='".$order_id."'");
    // Эту проверку можно вынести в метод checkDBResult. Так как это строка очень часто используется.
    if (gettype($books_list) == "boolean" || $books_list->num_rows == 0) return false;

    while ($book = $books_list->fetch_assoc()) {
      $book_price = $db->query("SELECT price FROM ".DB_TABLES["book"]." WHERE id='".$book["book_id"]."'");
      $count += (int)$book_price->fetch_assoc()["price"] * $book["count"];
    }

    return $count;
  }

  public static function getBooksListFromOrder($order_id) { // Используется при добавлении всех товаров из заказа в корзину
    $db = DB::getInstance();
    $books_list = array();

    if (!self::existsOrder($order_id)) return false;

    $mysql_books_list = $db->query("SELECT book_id, count FROM ".DB_TABLES["book_io"].
                                                        " WHERE order_id='".$order_id."'");
    if (!DB::checkDBResult($mysql_books_list)) return false;

    while ($book = $mysql_books_list->fetch_assoc())
      $books_list[] = array("id" => $book["book_id"], "count" => $book["count"]);

    return $books_list;
  }

  public static function getStatusForOrder($order_id) {
    $db = DB::getInstance();
    $status = $db->query("SELECT status FROM ".DB_TABLES["order"]." WHERE id='".$order_id."' LIMIT 1");
    if (!DB::checkDBResult($status)) return false;

    return $status->fetch_assoc()["id"];
  }

  public static function existsOrder($order_id) {
    $db = DB::getInstance();

    $exists_order = $db->query("SELECT id FROM ".DB_TABLES["order"]." WHERE id='".$order_id."' LIMIT 1");
    if (!DB::checkDBResult($exists_order)) return false;
    return true;
  }

  public static function getLastOrderId() { // Метод нужно протестировать. Не факт, что он корректно работает
    $db = DB::getInstance();

    $order_id = $db->query("SELECT id FROM ".DB_TABLES["order"]." ORDER BY id DESC LIMIT 1");
    if (!DB::checkDBResult($order_id)) return false;
    return $order_id->fetch_assoc()["id"];
  }
}

?>
