<?php

/*
  Данный модуль содержит классы необходимые для формирования страницы.
  Все написанные классы используются только для создания внешнего вида.
  Никакой бизнес-лоигики в этом модуле нет.
*/

namespace PageBuilder;

require_once(VIEW_MODULES_DIR."cart.class.php");
require_once(VIEW_MODULES_DIR."info_block.class.php");

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
        require_once(VIEW_MODULES_DIR."catalog.class.php");
        $this->title .= "Главная";
        $this->section_name = "Главная: перечень всех товаров";
        $this->content = Catalog::getHTML($page_info);
        break;

      case 2: // Страница конкретного жанра
        require_once(VIEW_MODULES_DIR."genre.class.php");
        require_once(VIEW_MODULES_DIR."catalog.class.php");
        $genre_name = Genre::getName($page_info["item_code"]);
        $this->title .= $genre_name;
        $this->section_name = "Жанр: ".$genre_name;
        $this->content = Catalog::getHTML($page_info);
        break;

      case 3: // Страница: информация о товаре
        require_once(VIEW_MODULES_DIR."book.class.php");
        $this->title .= "Информация о товаре";
        $this->section_name = "Информация о товаре";
        $this->content = Book::getFullInfoHTML($page_info);
        break;

      case 4: // Страница: оформление заказа
        require_once(VIEW_MODULES_DIR."add_order.class.php");
        $this->title .= "Оформление заказа";
        $this->section_name = "Оформление заказа";
        $this->content = AddOrder::getHTML($user); // Эта страница будет по разному отображаться для авторизованного/неавторизованного пользователя
        break;

      case 5: // Страница: регистрация
        require_once(VIEW_MODULES_DIR."register.class.php");
        $this->title .= "Регистрация нового пользователя";
        $this->section_name = "Регистрация нового пользователя";
        $this->content = Register::getHTML();
        break;

      case 6: // Личный кабинет
        switch ($page_info["item_code"]) {

          case 1:
            require_once(VIEW_MODULES_DIR."my_contacts.class.php");
            $this->title .= "Мои контакты";
            $this->section_name = "Мои контакты";
            $this->content = MyContacts::getHTML($user->getDataArr());
            break;

          case 2:
            require_once(VIEW_MODULES_DIR."cart.class.php");
            $this->title .= "Моя корзина";
            $this->section_name = "Моя корзина";
            $this->content = Cart::getShoppingList($user->getId());
            break;

          case 3:
            require_once(VIEW_MODULES_DIR."orders_history.class.php");
            $this->title .= "История заказов";
            $this->section_name = "История заказов";
            $this->content = OrdersHistory::getListHTML($user->getId());
            break;
        }
        break;

      case 7: // Страница: информация о заказе
        require_once(VIEW_MODULES_DIR."order_info.class.php");
        $this->title .= "Информация о заказе";
        $this->section_name = "Информация о заказе";
        $this->content = OrderInfo::getHTML($page_info["item_code"]);
        break;

      case 8: // Панель управления
        switch ($page_info["item_code"]) {

          case 1:
            require_once(VIEW_MODULES_DIR."control_panel.class.php");
            $this->title .= "Управление товарами";
            $this->section_name = "Управление товарами";
            $this->content = ControlPanel::getBooksListHTML($page_info["page_num"], 12);
            break;

          case 2:
            require_once(VIEW_MODULES_DIR."control_panel.class.php");
            $this->title .= "Управление пользователями";
            $this->section_name = "Управление пользователями";
            $this->content = ControlPanel::getUsersListHTML($page_info["page_num"], 12);
            break;

          case 3:
            require_once(VIEW_MODULES_DIR."control_panel.class.php");
            $this->title .= "Управление информационными блоками";
            $this->section_name = "Управление информационными блоками";
            $this->content = ControlPanel::getInfoBlocksListHTML($page_info["page_num"], 12);
            break;

          case 4:
            require_once(VIEW_MODULES_DIR."control_panel.class.php");
            $this->title .= "Панель управления";
            $this->section_name = "Панель управления";
            $this->content = ControlPanel::getMenuHTML();
            break;

          case 5:
            require_once(VIEW_MODULES_DIR."control_panel.class.php");
            $this->title .= "Управление заказами";
            $this->section_name = "Управление заказами";
            $this->content = ControlPanel::getOrdersListHTML($page_info["page_num"], 12);
            break;

          case 6:
            require_once(VIEW_MODULES_DIR."control_panel.class.php");
            $this->title .= "Управление жанрами";
            $this->section_name = "Управление жанрами";
            $this->content = ControlPanel::getGenresListHTML($page_info["page_num"], 12);
            break;
        }
        break;

      case 9:
        switch ($page_info["item_code"]) {
          case 1:
            require_once(VIEW_MODULES_DIR."books_editor.class.php");
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
            require_once(VIEW_MODULES_DIR."users_editor.class.php");
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
            require_once(VIEW_MODULES_DIR."info_blocks_editor.class.php");
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
            require_once(VIEW_MODULES_DIR."orders_editor.class.php");
            $this->title .= "Редактирование заказа";
            $this->section_name = "Редактирование заказа";
            $this->content = OrdersEditor::getEditHTML($page_info["page_num"], true);
            break;

          case 5:
            require_once(VIEW_MODULES_DIR."genres_editor.class.php");
            if ($page_info["page_num"] != 0) {
              $this->title .= "Редактирование жанра";
              $this->section_name = "Редактирование жанра";
              $this->content = GenresEditor::getEditHTML($page_info["page_num"]);
            } else {
              $this->title .= "Добавление жанра";
              $this->section_name = "Добавление жанра";
              $this->content = GenresEditor::getNewHTML();
            }
            break;
        }
        break;

      case 10:
        require_once(VIEW_MODULES_DIR."search.class.php");
        $this->title .= "Поиск товаров";
        $this->section_name = "Поиск: ".$page_info["item_code"];
        $this->content = Search::getBooksBySearchString($page_info["item_code"], $page_info["page_num"]);
        break;

      case 11:
        require_once(VIEW_MODULES_DIR."cart.class.php");
        $this->title .= "Моя корзина";
        $this->section_name = "Моя корзина";
        $this->content = Cart::getNoDBShoppingList();
        break;

      case 12:
        require_once(VIEW_MODULES_DIR."control_panel.class.php");
        $this->title .= "Поиск пользователей";
        $this->section_name = "Поиск пользователей";
        $this->content = ControlPanel::getSearchResultsByUsers($page_info["item_code"], $page_info["page_num"], 12);
        break;

      case 13:
        require_once(VIEW_MODULES_DIR."control_panel.class.php");
        $this->title .= "Поиск по товарам";
        $this->section_name = "Поиск по товарам";
        $this->content = ControlPanel::getSearchResultsByProducts($page_info["item_code"], $page_info["page_num"], 12);
        break;

      case 14:
        require_once(VIEW_MODULES_DIR."control_panel.class.php");
        $this->title .= "Поиск инфо-блоков";
        $this->section_name = "Поиск инфо-блоков";
        $this->content = ControlPanel::getSearchResultsByInfoBlocks($page_info["item_code"], $page_info["page_num"], 12);
        break;

      case 15:
        require_once(VIEW_MODULES_DIR."control_panel.class.php");
        $this->title .= "Поиск по жанрам";
        $this->section_name = "Поиск по жанрам";
        $this->content = ControlPanel::getSearchResultsByGenres($page_info["item_code"], $page_info["page_num"], 12);
        break;

      case 16:
        require_once(VIEW_MODULES_DIR."control_panel.class.php");
        $this->title .= "Поиск по заказам";
        $this->section_name = "Поиск по заказам";
        $this->content = ControlPanel::getSearchResultsByOrders($page_info["item_code"], $page_info["page_num"], 12);
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



?>
