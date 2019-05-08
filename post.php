<?php

function validateProfileData() {
  $errors = array();

  if (isset($_POST["firstname"], $_POST["lastname"], $_POST["gender"], $_POST["phone_number"], $_POST["email"],
  $_POST["zip_code"], $_POST["state"], $_POST["city"], $_POST["address"])) {

     $fname = $_POST["firstname"];
     $lname = $_POST["lastname"];
     $gender = $_POST["gender"];
     $phone = $_POST["phone_number"];
     $email = $_POST["email"];
     $zcode = $_POST["zip_code"];
     $state = $_POST["state"];
     $city = $_POST["city"];
     $address = $_POST["address"];

     if (mb_strlen($fname, "utf-8") == 0 && mb_strlen($fname, "utf-8") > 32) {

       array_push($errors, "Ошибка! Длина имени не может быть равна 0 и превышать 32 символа!");

     } elseif (mb_strlen($lname, "utf-8") == 0 && mb_strlen($lname, "utf-8") > 32){

       array_push($errors, "Ошибка! Длина фамилии не может быть равна 0 и превышать 32 символа!");

     } elseif ($gender != 1 && $gender != 2) {

       array_push($errors, "Ошибка! Некорректное значение пола!");

     } elseif (mb_strlen($lname, "utf-8") == 0 && mb_strlen($lname, "utf-8") == 0) {

       array_push($errors, "Ошибка! Некорректное значение пола!");

     } elseif ($gender != 1 && $gender != 2) {

       array_push($errors, "Ошибка! Некорректное значение пола!");

     } elseif ($gender != 1 && $gender != 2) {

       array_push($errors, "Ошибка! Некорректное значение пола!");

     } elseif ($gender != 1 && $gender != 2) {

       array_push($errors, "Ошибка! Некорректное значение пола!");

     }
   } // else определены не все переменные
}

switch ($_POST["form_name"]) {
  case "order":
    header("Location: /order/".$_POST["item_id"]);
    break;
  case "control_user":

    if ( isset($_POST["new"]) )
      header("Location: /edit/user/0");
    elseif ( isset($_POST["remove_all"]) ) User::clear();
    else {
      switch ($_POST["action"]) {
        case 'edit_row':
          header("Location: /edit/user/".$_POST["item_id"]);
          break;
        case "delete_row":
          User::remove($_POST["item_id"]); // Прежде чем удалять пользователя, необходимо удалить все связанные с ним поля
          break;
      }
    }
    break;

  case "edit_order":
    header("Location: /edit/order/".$_POST["item_id"]);
    break;

  case "control_products":
    require_once(MODULES_DIR."book.class.php");
    if ( isset($_POST["new"]) )
      header("Location: /edit/product/0");
    elseif ( isset($_POST["remove_all"]) ) Book::clean();
    else {
      switch ($_POST["action"]) {
        case 'edit_row':
          header("Location: /edit/user/".$_POST["item_id"]);
          break;
        case "delete_row":
          Book::removeById($_POST["item_id"]); // Прежде чем удалять товар, необходимо удалить все связанные с ним поля
          break;
      }
    }
    break;
  case "control_info_blocks":
    require_once(MODULES_DIR."info_block.class.php");
    if ( isset($_POST["new"]) )
      header("Location: /edit/info-block/0");
    elseif ( isset($_POST["remove_all"]) ) InfoBlock::clean();
    else {
      switch ($_POST["action"]) {
        case 'edit_row':
          header("Location: /edit/info-block/".$_POST["item_id"]);
          break;
        case "delete_row":
          InfoBlock::removeById($_POST["item_id"]); // Прежде чем удалять блок, необходимо удалить все связанные с ним поля
          break;
      }
    }
    break;
}

 ?>
