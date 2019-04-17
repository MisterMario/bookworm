<?php

// Скрипт возвращает в ответ JSON объект с данными
// Код необходимо доработать и довести до приличного вида. Так как писался исключительно на скорость.

$data = json_decode(file_get_contents("php://input"), true);
$answer = false;

require_once("wr_config.php");
require_once(MODULES_DIR."auth.class.php");
require_once(MODULES_DIR."user.class.php");
require_once(MODULES_DIR."cart.class.php");
use CartControl\Cart as CCart;

session_start();
$user = Auth::createUser();

if (isset($data["form_name"]) && $data["form_name"] == "add_to_cart" && isset($data["item_id"]) && isset($data["count"])) {

  if ($user) {

    if ( CCart::add($user->getId(), $data["item_id"], $data["count"]) ) {

      $cart_status = CCart::getStatusForUser($user->getId()); // Получение состояния корзины для конкртеного пользователя
      $answer = array("status" => true,"total_sum" => $cart_status["total_sum"], "count" => $cart_status["count"]);

    } else $answer = array("status" => false,"total_sum" => 0, "count" => 0);

  } else $answer = array("status" => false,"total_sum" => 0, "count" => 0);

} elseif (isset($data["form_name"]) && $data["form_name"] == "clear_cart") {

  if ($user) {

    if ( CCart::clear($user->getId()) )
      $answer = array("status" => true);
    else $answer = array("status" => false);

  } else $answer = array("status" => false);

} elseif (isset($data["form_name"]) && $data["form_name"] == "remove_from_cart" && isset($data["item_id"])) {

  if ($user) {

    if ( CCart::removeById( $data["item_id"], $user->getId()) )
      $answer = array("status" => true);
    else $answer = array("status" => false);

  } else $answer = array("status" => false);

} elseif (isset($data["form_name"]) && $data["form_name"] == "get_book_price" && isset($data["item_id"])) {

  $db = DB::getInstance();
  $price = $db->query("SELECT price FROM ".DB_TABLES["book"]." WHERE id='".$data["item_id"]."' LIMIT 1");

  if (gettype($price) != "boolean" && $price->num_rows != 0)
    $answer = array("status" => true, "price" => $price->fetch_assoc()["price"]);
  else $answer = array("status" => false, "price" => 0);
}

echo json_encode($answer);

?>
