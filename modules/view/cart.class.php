<?php

namespace PageBuilder;

require_once("book.class.php");

use DB;


class Cart {
  public static function getShoppingList($user_id) {
    $products_list = "";
    $total_sum = 0;
    $num_books = 0;
    $isNoDB = false; // Определяет: это корзина авторизованного пользователя или нет

    $db = DB::getInstance();
    $res = $db->query("SELECT id, book_id, count FROM ".DB_TABLES["book_ic"]." WHERE user_id=".$user_id);
    if (gettype($res) == "boolean" || $res->num_rows == 0) return EmptyContent::getHTML(1);

    while ($row = $res->fetch_assoc()) {
      $book = $db->query("SELECT name, author, price FROM ".DB_TABLES["book"]." WHERE id=".$row["book_id"]." LIMIT 1");
      if (gettype($book) == "boolean" || $book->num_rows == 0) continue;
      $book = $book->fetch_assoc();

      $book = array("item_id" => $row["id"], "book_id" => $row["book_id"], "name" => $book["name"], "author" => $book["author"],
                    "price" => $book["price"], "count" => $row["count"], "image" => Book::getImage($row["book_id"]));
      $book["total_sum"] = (int)$book["price"] * (int)$book["count"];
      $total_sum += $book["total_sum"];
      $num_books += (int)$book["count"];

      ob_start(); include SERVER_VIEW_DIR."book_in_cart.html";
      $products_list .= ob_get_clean();
    }

    ob_start(); include SERVER_VIEW_DIR."cart.html";
    return ob_get_clean();
  }

  public static function getNoDBShoppingList() {
    $products_list = "";
    $total_sum = 0;
    $num_books = 0;
    $books_arr = null;
    $isNoDB = true; // Определяет: это корзина авторизованного пользователя или нет

    if (isset($_COOKIE["bw-nodb-cart"])) {
      preg_match_all("/[0-9]+:[0-9]+/", $_COOKIE["bw-nodb-cart"], $books_arr);

      $books_arr = $books_arr[0];

      for ($i = 0; $i < count($books_arr); $i++) {

        $book_id = substr($books_arr[$i], 0, strpos($books_arr[$i], ":"));
        $book_count = substr($books_arr[$i], strpos($books_arr[$i], ":") + 1);

        $db = DB::getInstance();
        $book = $db->query("SELECT name, author, price FROM ".DB_TABLES["book"]." WHERE id=".$book_id." LIMIT 1");
        if (gettype($book) == "boolean" || $book->num_rows == 0) continue;
        $book = $book->fetch_assoc();

        $book = array("item_id" => $book_id, "book_id" => $book_id, "name" => $book["name"], "author" => $book["author"],
                      "price" => $book["price"], "count" => $book_count, "image" => Book::getImage($book_id));
        $book["total_sum"] = (int)$book["price"] * (int)$book_count;
        $total_sum += $book["total_sum"];
        $num_books += (int)$book_count;

        ob_start(); include SERVER_VIEW_DIR."book_in_cart.html";
        $products_list .= ob_get_clean();
      }
    } else return EmptyContent::getHTML(1);

    ob_start(); include SERVER_VIEW_DIR."cart.html";
    return ob_get_clean();
  }

  public static function getMiniCart($user_id) {
    $total_sum = 0;
    $num_books = 0;
    $db = DB::getInstance();

    if ($user_id) { // Мини-корзина для авторизованного пользователя

      $books = $db->query("SELECT book_id, count FROM ".DB_TABLES["book_ic"]." WHERE user_id=".$user_id);
      if (gettype($books) != "boolean" || $books->num_rows != 0) {

        while($row = $books->fetch_assoc()) {
          $num_books += (int)$row["count"];

          $book_price = $db->query("SELECT price FROM ".DB_TABLES["book"]." WHERE id=".$row["book_id"]." LIMIT 1");
          if (gettype($book_price) == "boolean" || $book_price->num_rows == 0) continue; // Если такого товара - пропустить итерацию

          $total_sum += (int)$book_price->fetch_assoc()["price"] * (int)$row["count"];
        }

      }

    } elseif (isset($_COOKIE["bw-nodb-cart"])) { // Мини-корзина для неавторизованного пользователя грузится из кук. Если они существуют

      $nums = array();

      // Извлекаем из кук числовые пары "ID товара: количество"
      preg_match_all("/[0-9]+/", $_COOKIE["bw-nodb-cart"], $nums);
      $nums = $nums[0];

      if (count($nums) > 0)
        for ($i = 0; $i < count($nums); $i++) {

          if (($i+1) % 2 != 0) { // Если текущий ключ: ID товара
            $book_price = $db->query("SELECT price FROM ".DB_TABLES["book"]." WHERE id=".$nums[$i]." LIMIT 1");
            if (gettype($book_price) == "boolean" || $book_price->num_rows == 0) continue; // Если такого товара - пропустить итерацию
            $total_sum += (int)$book_price->fetch_assoc()["price"] * $nums[$i+1];
          } else {
            $num_books += (int)$nums[$i];
          }
        }
    }

    ob_start(); include SERVER_VIEW_DIR."mini_cart.html";
    return ob_get_clean();
  }
}

 ?>
