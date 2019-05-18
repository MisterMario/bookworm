<?php

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
        case "search_item":
          header("Location: /control/users/search/".$_POST["search-string"]);
          break;
        case "edit_row":
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
        case "search_item":
          header("Location: /control/products/search/".$_POST["search-string"]);
          break;
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
        case "search_item":
          header("Location: /control/info-blocks/search/".$_POST["search-string"]);
          break;
        case 'edit_row':
          header("Location: /edit/info-block/".$_POST["item_id"]);
          break;
        case "delete_row":
          InfoBlock::removeById($_POST["item_id"]); // Прежде чем удалять блок, необходимо удалить все связанные с ним поля
          break;
      }
    }
    break;

  case "control_genres":
    require_once(MODULES_DIR."genre.class.php");
    if ( isset($_POST["new"]) )
      header("Location: /edit/genre/0");
    elseif ( isset($_POST["remove_all"]) ) Genre::clean();
    else {
      switch ($_POST["action"]) {
        case "search_item":
          header("Location: /control/genres/search/".$_POST["search-string"]);
          break;
        case 'edit_row':
          header("Location: /edit/genre/".$_POST["item_id"]);
          break;
        case "delete_row":
          Genre::removeById($_POST["item_id"]);
          break;
      }
    }
    break;
}

 ?>
