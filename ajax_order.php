<?php

// Скрипт возвращает в ответ JSON объект с данными
// Скрипт служит для добавления нового заказа для авторизованного или неавторизованного пользователя

$data = json_decode(file_get_contents("php://input"), true);
$answer = array("status" => false,"message" => "");

require_once("config.php");
require_once(MODULES_DIR."auth.class.php");
require_once(MODULES_DIR."user.class.php");
require_once(MODULES_DIR."cart.class.php");
require_once(MODULES_DIR."order.class.php");
require_once(MODULES_DIR."customer.class.php");
require_once(MODULES_DIR."validate.class.php");
use CartControl\Cart as CCart;

session_start();
$user = Auth::createUser();

if ($data["mode"] == "order_add") {

  $answer["message"] = Validate::order($data);
  if (strlen($answer["message"]) == 0) {

    if ($user) {
      if (!CCart::cartIsEmpty($user->getId())) {
        $cart_status = CCart::getStatusForUser($user->getId());

        $data["user_id"] = $user->getId();
        $data["books_of_count"] = $cart_status["count"];
        $data["total_price"] = $cart_status["total_sum"];
        $data["date_of_issue"] = date("Y-m-d");
        $data["date_of_dilivery"] = date("Y-m-d", strtotime("+".$data["date_of_dilivery"]." day"));
        if ($data["payment_system"] != null)
          $data["payment_method"] = (int)$data["payment_method"] + (int)$data["payment_system"] - 1;

        $answer["status"] = Order::add($data, CCart::getBooksListForUser($user->getId()));
        if ($answer["status"])
          CCart::clear($user->getId());
        else $answer["message"] = "Ошибка при добавлении заказа!";

      } else $answer = array("status" => false, "message" => "Ошибка! Невозможно оформить закзаз, так как ваша корзина пуста!");

    }
  } else $answer["status"] = false;

} elseif ($data["mode"] == "add_order_for_nodb_user") {

  if (!$user) {

    $answer["message"] = Validate::order($data);
    if (strlen($answer["message"]) == 0)
      $answer["message"] = Validate::userContacts($data["user_info"], false);

    if (strlen($answer["message"]) == 0) {

      if (!CCart::noDBCartIsEmpty()) {
        $cart_status = CCart::getStatusForNoDBUser();

        $data["user_id"] = NODB_USER_ID;
        $data["books_of_count"] = $cart_status["count"];
        $data["total_price"] = $cart_status["total_sum"];
        $data["date_of_issue"] = date("Y-m-d");
        $data["date_of_dilivery"] = date("Y-m-d", strtotime("+".$data["date_of_dilivery"]." day"));
        $data["user_info"]["phone_number"] = $data["user_info"]["phone"];

        $answer["status"] = Order::add($data, CCart::getBooksListForNoDBUser());

        if ($answer["status"]) {
          $order_id = Order::getLastOrderId();
          $answer["status"] = Customer::add($order_id, $data["user_info"]);

          if (!$answer["status"])
            $answer["message"] = "Ошибка при добавлении информации о покупателе!";
          else CCart::cleanNoDBCart();

        } else $answer["message"] = "Ошибка при добавлении заказа!";

      } else $answer = array("status" => false, "message" => "Ошибка! Невозможно оформить закзаз, так как ваша корзина пуста!");

    }
  } else $answer["message"] = "Ошибка! Эта функция недоступна для авторизованного пользователя!";

} elseif (!$user) {

  $data["message"] = "Эта функция недоступна для неавторизованного пользователя!";

} elseif ($data["mode"] == "add_books_from_order_to_cart") {

  $books_list = Order::getBooksListFromOrder($data["order_id"]);
  if ($books_list) {
    $answer["status"] = CCart::addBooks($user->getId(), $books_list);

    if ($answer["status"]) {
      $cart_status = CCart::getStatusForUser($user->getId());
      $answer["total_sum"] = $cart_status["total_sum"];
      $answer["count"] = $cart_status["count"];

    } else $answer["message"] = "Сервер: Не удается добавить список книг в корзину!";

  } else $answer["message"] = "Сервер: Не удается получить список книг из заказа!";

} elseif ($data["mode"] == "cancel_order") {

  $answer["status"] = Order::changeStatus($data["order_id"], ORDER_STATUS[count(ORDER_STATUS) - 1]);
  if (!$answer["status"]) $answer["message"] = "Сервер: Не удается сменить статус заказа!";

}

echo json_encode($answer);

 ?>
