<?php

/*
  Данный модуль содержит классы необходимые для формирования страницы.
  Все написанные классы используются только для создания внешнего вида.
  Никакой бизнес-лоигики в этом модуле нет.
*/

namespace PageBuilder;

require_once(MODULES_DIR."cart.class.php");
use CartControl\Cart as CCart;
use DB;
use User;

class Page {
  public $code = 0;
  public $title = SITE_NAME." - ";
  public $charset = SITE_CHARSET;
  public $mini_profil;
  public $mini_cart;
  public $topmenu;
  public $left_block;
  public $section_name;
  public $content;

  public function __construct($page_info, $user) {
    $this->code = $page_info["page_code"];

    // Установка полей: title и section_name
    switch($page_info["page_code"]) {
      case 1: // Страница: главная
        $this->title .= "Главная";
        $this->section_name = "Главная: перечень всех товаров";
        $this->content = Catalog::getHTML($page_info);
        break;
      case 2: // Страница конкретного жанра
        $genre_name = Genre::getName($page_info["item_code"]);
        $this->title .= $genre_name;
        $this->section_name = "Жанр: ".$genre_name;
        $this->content = Catalog::getHTML($page_info);
        break;
      case 3: // Страница: информация о товаре
        $this->title .= "Информация о товаре";
        $this->section_name = "Информация о товаре";
        $this->content = Book::getFullInfoHTML($page_info);
        break;
      case 4: // Страница: оформление заказа
        $this->title .= "Оформление заказа";
        $this->section_name = "Оформление заказа";
        $this->content = AddOrder::getHTML($user); // Эта страница будет по разному отображаться для авторизованного/неавторизованного пользователя
        break;
      case 5: // Страница: регистрация
        $this->title .= "Регистрация нового пользователя";
        $this->section_name = "Регистрация нового пользователя";
        $this->content = Register::getHTML();
        break;
      case 6: // Личный кабинет
        switch ($page_info["item_code"]) {
          case 1:
            $this->title .= "Мои контакты";
            $this->section_name = "Мои контакты";
            $this->content = MyContacts::getHTML($user->getDataArr());
            break;
          case 2:
            $this->title .= "Моя корзина";
            $this->section_name = "Моя корзина";
            $this->content = Cart::getShoppingList($user->getId());
            break;
          case 3:
            $this->title .= "История заказов";
            $this->section_name = "История заказов";
            $this->content = OrdersHistory::getListHTML($user->getId());
            break;
        }
        break;
      case 7: // Страница: информация о заказе
        $this->title .= "Информация о заказе";
        $this->section_name = "Информация о заказе";
        $this->content = OrderInfo::getHTML($page_info["item_code"]);
        break;
      case 8: // Панель управления
        switch ($page_info["item_code"]) {
          case 1:
            $this->title .= "Управление товарами";
            $this->section_name = "Управление товарами";
            $this->content = ControlPanel::getBooksListHTML($page_info["page_num"], 12);
            break;
          case 2:
            $this->title .= "Управление пользователями";
            $this->section_name = "Управление пользователями";
            $this->content = ControlPanel::getUsersListHTML($page_info["page_num"], 12);
            break;
          case 3:
            $this->title .= "Управление информационными блоками";
            $this->section_name = "Управление информационными блоками";
            $this->content = ControlPanel::getInfoBlocksListHTML($page_info["page_num"], 12);
            break;
          case 4:
            $this->title .= "Панель управления";
            $this->section_name = "Панель управления";
            $this->content = ControlPanel::getMenuHTML();
            break;
        }
        break;
      case 9:
        switch ($page_info["item_code"]) {
          case 1:
            if ($page_info["page_num"] != 0) {
              $this->title .= "Редактирование информации о товаре";
              $this->section_name = "Редактирование информации о товаре";
              $this->content = BooksEditor::getEditHTML($page_info["page_num"]);
            } else {
              $this->title .= "Добавление нового товара";
              $this->section_name = "Добавление нового товара";
              $this->content = BooksEditor::getNewHTML();
            }
            break;
          case 2:
            if ($page_info["page_num"] != 0) {
              $this->title .= "Редактирование информации о пользователе";
              $this->section_name = "Редактирование информации о пользователе";
              $this->content = UsersEditor::getEditHTML($page_info["page_num"]);
            } else {
              $this->title .= "Создание нового аккаунта";
              $this->section_name = "Создание нового аккаунта";
              $this->content = UsersEditor::getNewHTML();
            }
            break;
          case 3:
            if ($page_info["page_num"] != 0) {
              $this->title .= "Редактирование информационного блока";
              $this->section_name = "Редактирование информационного блока";
              $this->content = InfoBlocksEditor::getEditHTML($page_info["page_num"]);
            } else {
              $this->title .= "Создание нового информационного блока";
              $this->section_name = "Создание нового информационного блока";
              $this->content = InfoBlocksEditor::getNewHTML();
            }
            break;
          case 4:
            $this->title .= "Редактирование заказа";
            $this->section_name = "Редактирование заказа";
            $this->content = OrderInfo::getEditHTML($page_info["page_num"], true);
            break;
        }
        break;
      case 10:
        $this->title .= "Поиск товаров";
        $this->section_name = "Поиск: ".$page_info["item_code"];
        $this->content = Search::getBooksBySearchString($page_info["item_code"], $page_info["page_num"]);
        break;
      case 11:
        $this->title .= "Моя корзина";
        $this->section_name = "Моя корзина";
        $this->content = Cart::getNoDBShoppingList();
        break;
      case 403:
        $this->title .= "Ошибка доступа!";
    }

    if ($page_info["page_code"] != 403 && $page_info["page_code"] != 404) {
      // Мини-профиль
      ob_start();
      $this->mini_profil = self::getMiniProfil($user);

      // Мини-корзина
      $this->mini_cart = Cart::getMiniCart($user ? $user->getId() : false);

      // Верхнее меню
      if ($user) $this->topmenu = "<li><a href=\"/pc/my-contacts\">Личный кабинет</a></li>";
      else $this->topmenu = "<li><a href=\"/cart/\">Корзина</a></li><li><a href=\"/registration/\">Регистрация</a></li>";

      // Левый блок контента (инфо-блоки)
      $this->left_block = InfoBlock::getBlocksListHTML($user ? $user->getLevel() : 0);
    }
  }

  public function getHTML() {
    ob_start();
    switch ($this->code) {
      case 403:
        include SERVER_VIEW_DIR."403.html";
        break;
      case 404:
        include SERVER_VIEW_DIR."404.html";
        break;
      default:
        include SERVER_VIEW_DIR."index.html";
        break;
    }
    return ob_get_clean();
  }

  public static function getPageNavigation($num_pages, $active_num, $uri = "") { // Возвращает элементы перемещения между страницами чего-либо
    $list = "";
    $min = $active_num - 1; $max = $active_num + 1;
    if ($active_num > $num_pages) return null; // Если активной страницы не существует

    // Расстановка элементов до центральных
    if ($active_num - 3 > 0) $list .="<li><a href=\"".$uri."1\">1</a></li>\n<li class=\"disabled\">...</li>";
    elseif ($active_num - 3 == 0) $list .= "<li><a href=\"".$uri."1\">1</a></li>";
    elseif ($active_num == 1) $min = 1;
    if ($active_num == $num_pages) $max = $active_num;
    // Расстановка центральных элементов
    for ($i = $min; $i <= $max; $i++) {
      if ($i != $active_num) $list .= "<li><a href=\"".$uri.$i."\">".$i."</a></li>";
      else $list .= "<li class=\"active\">".$i."</li>";
    }
    // Растновка элементов после центра
    if ($active_num + 3 < $num_pages) $list .= "<li class=\"disabled\">...</li><li><a href=\"\">".$num_pages."</a></li>";
    elseif ($active_num + 2 == $num_pages) $list .= "<li><a href=\"".$uri.$num_pages."\">".$num_pages."</a></li>";

    ob_start(); include SERVER_VIEW_DIR."pages_navigation.html";
    return ob_get_clean();
  }

  public static function getMiniProfil($user) {
    ob_start();
    if ($user) include SERVER_VIEW_DIR."mp_user.html";
    else include SERVER_VIEW_DIR."mp_login.html";

    return ob_get_clean();
  }
}

class Catalog {
  public static function getHTML($page_info) { // Возвращает контент главной страницы (каталога всех товаров)
    $content = "";
    ob_start(); include SERVER_VIEW_DIR."sorting_goods.html";
    $content .= ob_get_clean();
    $content .= BooksCatalog::getFullBooksListHTML($page_info["page_num"], $page_info["item_code"]);

    $db = DB::getInstance();
    $num_pages = $db->query("SELECT COUNT(id) as count FROM ".DB_TABLES["book"].($page_info["page_code"] == 2 ? " WHERE genre_id=".$page_info["item_code"] : "") ); // Получение количества всех записей определенного жанра
    if (gettype($num_pages) == "boolean" || $num_pages->fetch_assoc()["count"] == 0) return EmptyContent::getHTML(2);
    $num_pages->data_seek(0);
    $num_pages = (int)ceil( ( (int)$num_pages->fetch_assoc()["count"] ) / 12 ); // Количество страниц

    if ($page_info["item_code"] == 0) $uri = "/catalog/";
    else $uri = "/genre/".$page_info["item_code"]."/";

    $content .= Page::getPageNavigation($num_pages, $page_info["page_num"], $uri);
    return $content;
  }
}

class InfoBlock {
  public static function getBlocksListHTML($user_level) {
    $blocks_list = "";
    $db = DB::getInstance();
    $res = $db->query("SELECT title, content FROM ".DB_TABLES["i-block"]." WHERE access_level <= ".$user_level);
    if (gettype($res) == "boolean" || $res->num_rows == 0) return null;
    while($row = $res->fetch_assoc()) {
      ob_start();
      $block_title = $row["title"];
      $block_content = htmlspecialchars_decode($row["content"], ENT_QUOTES);
      include SERVER_VIEW_DIR."info_block.html";
      $blocks_list .= ob_get_clean();
    }

    return $blocks_list;
  }
}

class BooksCatalog {
  public static function getFullBooksListHTML($page_num = 1, $genre_id = 0) {
    $books_list = ""; $books_row = ""; $i = 0; $first_id = 0;
    $db = DB::getInstance();

    if ($genre_id != 0) { // Если требуется получить книги конкретного жанра
      $first_id = $db->query("SELECT id FROM ".DB_TABLES["book"]." WHERE genre_id='".$genre_id."' LIMIT 1");
      if (gettype($first_id) != "boolean" || $first_id->num_rows != 0) $first_id = (int)$first_id->fetch_assoc()["id"]-1;
    }
    $first_id += ($page_num * 12) - 12 + 1; // Для того, чтобы получать книги только нужной страницы

    // Нужно сделать сортировку в обратном порядке, для того, чтобы сразу отображались самые новые товары, а затем старые
    $res = $db->query("SELECT id, name, author, price
                       FROM ".DB_TABLES["book"].
                       " WHERE id >= ".$first_id.
                       ($genre_id != 0 ? " AND genre_id=".$genre_id : "")." LIMIT 12"); // Если нужно выбирать книги конкретного жанра
    if (gettype($res) == "boolean" || $res->num_rows == 0) return null;
    while($row = $res->fetch_assoc()) {
      $i++;
      ob_start();
      $book = array("id" => $row["id"], "name" => self::bookName($row["name"]), "author" => self::bookAuthor($row["author"]), "price" => $row["price"], "image" => Book::getImage($row["id"]));
      include SERVER_VIEW_DIR."small_book.html";
      $books_row .= ob_get_clean();
      if ($i % 4 == 0 || $i == $res->num_rows) { // Строка добавляется в каталог неполной, если книга последняя.
        ob_start();
        include SERVER_VIEW_DIR."small_books_row.html";
        $books_list .= ob_get_clean(); $books_row = ""; // Добавляет текущую строку с книгами в каталог и очишает ее
      }
    }
    ob_start(); include SERVER_VIEW_DIR."catalog.html";

    return ob_get_clean();
  }

  public static function bookName($name) {
    return mb_strlen($name, "utf-8") > 22 ? mb_substr($name, 0, 20, "utf-8")."..." : $name;
  }

  public static function bookAuthor($author) { // Усекает имя автора, если оно больше определенного
    return mb_strlen($author, "utf-8") > 25 ? mb_substr($author, 0, 25, "utf-8")."..." : $author;
  }
}

class Genre {
  public static function getName($id) {
    $db = DB::getInstance();
    $res = $db->query("SELECT name FROM ".DB_TABLES["genre"]." WHERE id=".$id." LIMIT 1");
    if(gettype($res) == "boolean" || $res->num_rows == 0) return null;

    return $res->fetch_assoc()["name"];
  }

  public static function getIdByName($name) {
    $db = DB::getInstance();
    $genre_id = $db->query("SELECT id FROM ".DB_TABLES["genre"]." WHERE name LIKE '%${name}%' LIMIT 1");
    if (gettype($genre_id) == "boolean" || $genre_id->num_rows == 0) return null;

    return $genre_id->fetch_assoc()["id"];
  }

  public static function getGenreSelectHTML($selected_num = -1) {
    $list = "";
    $db = DB::getInstance();

    $res = $db->query("SELECT id, name FROM ".DB_TABLES["genre"]);
    if(gettype($res) == "boolean" || $res->num_rows == 0) return null;

    while($row = $res->fetch_assoc()) {
      if ((int)$selected_num == (int)$row["id"]) $list .= "<option value=\"".$row["id"]."\" selected>".$row["name"]."</option>";
      else $list .= "<option value=\"".$row["id"]."\">".$row["name"]."</option>";
    }

    return $list;
  }
}

class Book {
  public static function getFullInfoHTML($page_info) {
    global $user;

    $content = "";

    $book_general_information = self::getFullBookInfoHTML($page_info["item_code"]);
    $book_reviews = Review::getReviewsList($page_info["item_code"]);
    $my_review = "";
    if ($user) {
      ob_start(); include SERVER_VIEW_DIR."my_review.html";
      $my_review = ob_get_clean();
    }

    ob_start(); include SERVER_VIEW_DIR."book_info.html";
    return ob_get_clean();
  }

  public static function getFullBookInfoHTML($id) { // Возвращает блок с полной информацией о книге
    $db = DB::getInstance();
    $res = $db->query("SELECT * FROM ".DB_TABLES["book"]." WHERE id=".$id." LIMIT 1");
    if(gettype($res) == "boolean" || $res->num_rows == 0) return null;
    $res = $res->fetch_assoc();
    foreach($res as $key => $value){
      $book[$key] = ($value != null)? $value : "не указан.";
    }
    $book["genre"] = Genre::getName($book["genre_id"]);
    $book["image"] = self::getImage($book["id"]);
    ob_start();
    include SERVER_VIEW_DIR."book_general_information.html";
    return ob_get_clean();
  }

  public static function getImage($id) { // На случай, если для книги не было загружено картинки
    return file_exists(SERVER_VIEW_DIR."/products/".$id.".png")? $id.".png" : "default.png";
  }
}

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

class MyContacts {
  public static function getHTML($user_info) { // Принимает массив с информацией о пользователе
    ob_start(); include SERVER_VIEW_DIR."my_contacts.html";

    return ob_get_clean();
  }
}

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
                     "date_of_issue" => $row["date_of_issue"],
                     "date_of_dilivery" => $row["date_of_dilivery"],
                     "dilivery_time" => OrderInfo::getDiliveryTimeName($row["time_of_dilivery"]),
                     "dilivery_method" => OrderInfo::getDiliveryMethodName($row["dilivery_method"]),
                     "payment_method" => OrderInfo::getPaymentMethodName($row["payment_method"]),
                     "status_name" => OrderInfo::getStatusName($row["status"]));

      if ((int)$order["status"] < ORDER_STATUS[2]) { // Если заказ еще не в пути, то его можно изменить или отменить.
        $canBeEdited = true;
        $canCancel = true;
      }

      ob_start(); include SERVER_VIEW_DIR."order.html";
      $orders_list .= ob_get_clean();
    }

    ob_start(); include SERVER_VIEW_DIR."purchase_history.html";
    return ob_get_clean();
  }
}

class OrderInfo {
  public static function getHTML($order_id) {
    $db = DB::getInstance();
    $books_list = "";
    $isEdited = false;

    $order = self::getOrderInfo($order_id);
    if (!$order) return false;

    $books_from_order = $db->query("SELECT * FROM ".DB_TABLES["book_io"]." WHERE order_id=".$order_id);
    if (!DB::checkDBResult($books_from_order)) return false;

    while($book = $books_from_order->fetch_assoc()) {

      $book_info = $db->query("SELECT name, author, price FROM ".DB_TABLES["book"]." WHERE id=".$book["book_id"]." LIMIT 1");
      if (!DB::checkDBResult($book_info)) return null;
      $book_info = $book_info->fetch_assoc();

      $book = array("id" => $book["book_id"],
                    "name" => $book_info["name"],
                    "author" => $book_info["author"],
                    "price" => $book_info["price"],
                    "count" => $book["count"],
                    "total_sum" => (int)$book_info["price"] * (int)$book["count"],
                    "image" => Book::getImage($book["book_id"]));

      ob_start(); include SERVER_VIEW_DIR."book_in_order.html";
      $books_list .= ob_get_clean();
    }


    ob_start(); include SERVER_VIEW_DIR."information_about_order.html";
    return ob_get_clean();
  }

  public static function getEditHTML($order_id) {
    $db = DB::getInstance();
    $books_list = "";
    $isEdited = true;

    $order = self::getOrderInfo($order_id);
    if (!$order) return false;

    $books_from_order = $db->query("SELECT * FROM ".DB_TABLES["book_io"]." WHERE order_id=".$order_id);
    if (!DB::checkDBResult($books_from_order)) return false;

    while($book = $books_from_order->fetch_assoc()) {

      $book_info = $db->query("SELECT name, author, price FROM ".DB_TABLES["book"]." WHERE id=".$book["book_id"]." LIMIT 1");
      if (!DB::checkDBResult($book_info)) return null;
      $book_info = $book_info->fetch_assoc();

      $book = array("id" => $book["book_id"],
                    "name" => $book_info["name"],
                    "author" => $book_info["author"],
                    "price" => $book_info["price"],
                    "count" => $book["count"],
                    "total_sum" => (int)$book_info["price"] * (int)$book["count"],
                    "image" => Book::getImage($book["book_id"]));

      ob_start(); include SERVER_VIEW_DIR."edit_book_in_order.html";
      $books_list .= ob_get_clean();
    }

    ob_start(); include SERVER_VIEW_DIR."information_about_order.html";
    return ob_get_clean();
  }

  public static function getOrderInfo($order_id) {
    $db = DB::getInstance();

    $res = $db->query("SELECT * FROM ".DB_TABLES["order"]." WHERE id=".$order_id." LIMIT 1");
    if (!DB::checkDBResult($res)) return false;
    $res = $res->fetch_assoc();

    $order = array("id" => $res["id"],
                   "status" => $res["status"],
                   "num_books" => $res["books_of_count"],
                   "total_price" => $res["total_price"],
                   "date_of_issue" => $res["date_of_issue"],
                   "date_of_dilivery" => $res["date_of_dilivery"],
                   "dilivery_time" => self::getDiliveryTimeName($res["time_of_dilivery"]),
                   "dilivery_method" => self::getDiliveryMethodName($res["dilivery_method"]),
                   "payment_method" => self::getPaymentMethodName($res["payment_method"]),
                   "status_name" => self::getStatusName($res["status"]),
                   "canBeEdited" => false,
                   "canCancel" => false);

    if ((int)$order["status"] < ORDER_STATUS[2]) { // Если заказ еще не в пути, то его можно изменить или отменить.
      $order["canBeEdited"] = true;
      $order["canCancel"] = true;
    }

    return $order;
  }

  public static function getDiliveryTimeName($dilivery_time) {
    switch ($dilivery_time) {
      case '1': return "10:00-12:00";
      case '2': return "12:00-14:00";
      case '3': return "14:00-16:00";
      case '4': return "16:00-18:00";
    }
    return null;
  }

  public static function getDiliveryMethodName($dilivery_method) {
    switch ($dilivery_method) {
      case '1': return "курьерская доставка";
      case '2': return "почтовая доставка";
      case '3': return "самовывоз";
    }
    return null;
  }

  public static function getPaymentMethodName($payment_method) {
    switch ($payment_method) {
      case '1': return "наличными";
      case '2': return "банковской картой";
      case '3': return "онлайн-платеж (WM)";
      case '4': return "онлайн-платеж (PayPal)";
      case '5': return "онлайн-платеж (ЯД)";
      case '6': return "онлайн-платеж (Quwi)";
    }
    return null;
  }

  public static function getStatusName($status) {
    switch ($status) {
      case '1': return "ожидает подтверждения";
      case '2': return "укомплектован";
      case '3': return "находится в пути";
      case '4': return "выполнен";
      case '5': return "отменен";
    }
    return null;
  }
}

class Register {
  public static function getHTML() {
    ob_start(); include SERVER_VIEW_DIR."register.html";
    return ob_get_clean();
  }
}

class ControlPanel {
  public static function getMenuHTML() {
    ob_start(); include SERVER_VIEW_DIR."control-panel.html";
    return ob_get_clean();
  }
  public static function getBooksListHTML($page_num, $count) {
    $db = DB::getInstance();
    $list = "";

    $offset = ($page_num * $count) - $count;
    $res = $db->query("SELECT id, name, author, price, count FROM ".DB_TABLES["book"]." ORDER BY id LIMIT ".$count." OFFSET ".$offset);
    if (gettype($res) == "boolean" || $res->num_rows == 0) return null;

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

    ob_start(); include SERVER_VIEW_DIR."cp_books.html";
    return ob_get_clean();
  }

  public static function getUsersListHTML($page_num, $count) {
    $db = DB::getInstance();
    $list = "";

    $offset = ($page_num * $count) - $count;
    $res = $db->query("SELECT id, firstname, lastname, email FROM ".DB_TABLES["user"]." ORDER BY id LIMIT ".$count." OFFSET ".$offset);
    if (gettype($res) == "boolean" || $res->num_rows == 0) return null;

    while ($row = $res->fetch_assoc()) {
      $user_info = array("id" => $row["id"], "firstname" => $row["firstname"], "lastname" => $row["lastname"], "email" => $row["email"]);
      ob_start(); include SERVER_VIEW_DIR."cp_small_user.html";
      $list .= ob_get_clean();
    }
    $num_pages = (int)( $db->query("SELECT count(id) FROM ".DB_TABLES["user"])->fetch_assoc()["count(id)"] ) / $count;
    $num_pages = (int)ceil($num_pages);
    $pages_navigation = Page::getPageNavigation($num_pages, $page_num, "/control/users/");

    ob_start(); include SERVER_VIEW_DIR."cp_users.html";
    return ob_get_clean();
  }

  public static function getInfoBlocksListHTML($page_num, $count) {
    $db = DB::getInstance();
    $list = "";

    $offset = ($page_num * $count) - $count;
    $res = $db->query("SELECT id, title, access_level FROM ".DB_TABLES["i-block"]." ORDER BY id LIMIT ".$count." OFFSET ".$offset);
    if (gettype($res) == "boolean" || $res->num_rows == 0) return null;

    while ($row = $res->fetch_assoc()) {
      $ib = array("id" => $row["id"], "title" => $row["title"], "access_level" => $row["access_level"]);
      ob_start(); include SERVER_VIEW_DIR."cp_small_info_block.html";
      $list .= ob_get_clean();
    }
    $num_pages = (int)( $db->query("SELECT count(id) FROM ".DB_TABLES["i-block"])->fetch_assoc()["count(id)"] ) / $count;
    $num_pages = (int)ceil($num_pages);
    $pages_navigation = Page::getPageNavigation($num_pages, $page_num, "/control/info-blocks/");

    ob_start(); include SERVER_VIEW_DIR."cp_info_blocks.html";
    return ob_get_clean();
  }
}

class UsersEditor {
  public static function getEditHTML($user_id) {
    $isEdit = true;
    $user_info = User::getUserInformation($user_id);

    ob_start(); include SERVER_VIEW_DIR."editor_user.html";
    return ob_get_clean();
  }

  public static function getNewHTML() {
    $isEdit = false;
    $user_info = array("id" => 0,
                       "firstname" => "",
                       "lastname" => "",
                       "email" => "",
                       "gender" => 1,
                       "phone_number" => "",
                       "level" => 1,
                       "state" => "",
                       "city" => "",
                       "address" => "",
                       "zip_code" => "");

    ob_start(); include SERVER_VIEW_DIR."editor_user.html";
    return ob_get_clean();
  }
}

class BooksEditor {
  public static function getEditHTML($id) {
    $db = DB::getInstance();
    $isEdit = true;

    $res = $db->query("SELECT * FROM ".DB_TABLES["book"]." WHERE ".DB_TABLES["book"].".id='".$id."' LIMIT 1");
    if (gettype($res) == "boolean" || $res->num_rows == 0) return null;
    $res = $res->fetch_assoc();

    $book = array("id" => $res["id"], "name" => $res["name"], "author" => $res["author"],
                  "language" => $res["language"], "keywords" => $res["tags"], "series" => $res["series"],
                  "rightholder" => $res["rightholder"], "age_restrictions" => $res["age_restrictions"], "isbn" => $res["isbn"],
                  "price" => $res["price"], "count" => $res["count"], "annotation" => $res["annotation"], "image" => Book::getImage($res["id"]));
    $genre_select = Genre::getGenreSelectHTML($res["genre_id"]);

    ob_start(); include SERVER_VIEW_DIR."editor_book.html";
    return ob_get_clean();
  }

  public static function getNewHTML() {
    $isEdit = false;

    $book = array("id" => 0, "name" => "", "author" => "", "genre" => "", "language" => "", "keywords" => "", "series" => "",
                  "rightholder" => "", "age_restrictions" => "", "isbn" => "", "price" => "", "count" => "", "annotation" => "", "image" => Book::getImage(0));
    $genre_select = Genre::getGenreSelectHTML();
    ob_start(); include SERVER_VIEW_DIR."editor_book.html";
    return ob_get_clean();
  }
}

class InfoBlocksEditor {
  public static function getEditHTML($id) {
    $db = DB::getInstance();
    $isEdit = true;

    $res = $db->query("SELECT * FROM ".DB_TABLES["i-block"]." WHERE id='".$id."' LIMIT 1");
    if (gettype($res) == "boolean" || $res->num_rows == 0) return null;
    $res = $res->fetch_assoc();

    $i_block = array("id" => $res["id"], "title" => $res["title"], "content" => $res["content"], "access_level" => $res["access_level"]);

    ob_start(); include SERVER_VIEW_DIR."editor_info_block.html";
    return ob_get_clean();
  }

  public static function getNewHTML() {
    $isEdit = false;

    $i_block = array("id" => 0, "title" => "", "content" => "", "access_level" => 0);
    ob_start(); include SERVER_VIEW_DIR."editor_info_block.html";
    return ob_get_clean();
  }
}

class EmptyContent {
  public static function getHTML($reason_id) {
    $reason_class = "";
    $reason_text = "";
    $html = "";

  	ob_start();
  	switch ($reason_id) {
      case 1:
        $reason_class = "empty-cart";
        $reason_text = "Корзина товаров пуста";
        break;
      case 2:
        $reason_class = "empty-catalog";
        $reason_text = "Каталог товаров пуст";
        break;
      case 3:
        $reason_class = "empty-orders-history";
        $reason_text = "Вы еще ничего не заказывали";
        break;
      case 4:
        $reason_class = "failed-search";
        $reason_text = "Поиск не дал результатов";
        break;
    }
    include SERVER_VIEW_DIR."empty_content.html";
  	$html = ob_get_clean();

    return $html;
  }
}

class Search {
  public static function getBooksBySearchString($searched_string, $page_num) {
    $content = "";
    $db = DB::getInstance();

    ob_start(); include SERVER_VIEW_DIR."sorting_goods.html";
    $content .= ob_get_clean();

    $first_id = ($page_num * 12) - 12 + 1;
    $genre_id = Genre::getIdByName($searched_string);
    $searched_books = $db->query("SELECT id, name, author, price FROM ".DB_TABLES["book"].
                                 " WHERE name LIKE '%${searched_string}%'".
                                 " OR author LIKE '%${searched_string}%'".
                                 (strlen($genre_id) != 0 ? " OR genre_id LIKE '${genre_id}'" : "").
                                 " AND id >= '${first_id}'".
                                 " LIMIT 12");
    if (gettype($searched_books) == "boolean" || $searched_books->num_rows == 0) return EmptyContent::getHTML(4);

    $books_list = ""; $books_row = ""; $i = 1;
    while ($book = $searched_books->fetch_assoc()) {
      ob_start();
      $book["name"] = BooksCatalog::bookName($book["name"]);
      $book["author"] = BooksCatalog::bookAuthor($book["author"]);
      $book["image"] = Book::getImage($book["id"]);
      include SERVER_VIEW_DIR."small_book.html";
      $books_row .= ob_get_clean();

      if ($i % 4 == 0 || $i == $searched_books->num_rows) { // Строка добавляется в каталог неполной, если книга последняя.
        ob_start();
        include SERVER_VIEW_DIR."small_books_row.html";
        $books_list .= ob_get_clean(); $books_row = ""; // Добавляет текущую строку с книгами в каталог и очишает ее
      }
      $i++;
      if ($i > 4) $i = 1;
    }
    ob_start(); include SERVER_VIEW_DIR."catalog.html";
    $content .= ob_get_clean();

    $searched_books_num = $db->query("SELECT COUNT(id) as count FROM ".DB_TABLES["book"].
                                     " WHERE name LIKE '%${searched_string}%'".
                                     " OR author LIKE '%${searched_string}%'".
                                     (strlen($genre_id) != 0 ? " OR genre_id LIKE '${genre_id}'" : "").
                                     " AND id > '${first_id}'".
                                     " LIMIT 12");
    if (gettype($searched_books_num) == "boolean" || $searched_books_num->num_rows == 0) return EmptyContent::getHTML(4);
    $num_pages = (int)ceil( $searched_books_num->fetch_assoc()["count"] / 12 ); // Количество страниц

    $uri = "/search/".$searched_string."/";

    $content .= Page::getPageNavigation($num_pages, $page_num, $uri);
    return $content;
  }

  public static function getResultsList($books_list) {
    $html = "";

    foreach ($books_list as $book) {
      $book["image"] = Book::getImage($book["id"]);
      if (mb_strlen($book["name"], "utf-8") > 25)
        $book["name"] = mb_substr($book["name"], 0, 23, "utf-8")."...";
      if (mb_strlen($book["author"], "utf-8") > 30)
        $book["author"] = mb_substr($book["author"], 0, 30, "utf-8")."...";
      ob_start(); include SERVER_VIEW_DIR."search-result-line.html";
      $html .= ob_get_clean();
    }
    return $html;
  }
}

?>
