<?php

# Данные для работы сайта
define("ROOT_DIR", $_SERVER["DOCUMENT_ROOT"]);
define("SERVER_VIEW_DIR", ROOT_DIR."view/");
define("CLIENT_VIEW_DIR", "/view/");
define("JS_DIR", SERVER_VIEW_DIR."js/");
define("MODULES_DIR", ROOT_DIR."modules/");
define("P_DIR", ROOT_DIR."products/");
define("MAX_FILE_SIZE", 500 * 1024); // Максимальный размер аватарки в КБ
define("MAX_FILE_WIDTH", 500);
define("MAX_FILE_HEIGHT", 500);
define("CURRENCY", "BYR");
define("SITE_NAME", "BookWorm.by");
define("SITE_CHARSET", "utf-8");
define("NODB_USER_ID", 1);

# Подключаемые скрипты
require_once(MODULES_DIR."db.class.php");
require_once(MODULES_DIR."SEF.php");
require_once(MODULES_DIR."page_builder.php");
require_once(MODULES_DIR."auth.class.php");
require_once(MODULES_DIR."user.class.php");

# Данные для связи с БД
define("DB_SETTINGS", array(
  "host" => "127.0.0.1",
  "user" => "root",
  "pass" => "",
  "name" => "internet_market"
));
define("DB_TABLES", array(
  "book" => "book",
  "book_ic" => "book_in_cart",
  "book_io" => "book_in_order",
  "dp" => "dynamic_page",
  "genre" => "genre",
  "i-block" => "info_block",
  "order" => "order_",
  "review" => "review",
  "user" => "user",
  "customer" => "customer",
));

# Правила доступности страниц для различных групп пользователей
define('PF_NO_USER', array(1, 2, 3, 4, 5, 10, 11)); // Для неавторизованного пользователя (анонима)
define('PF_SIMPLE_USER', array(1, 2, 3, 4, 6, 7, 10)); // Для обычного пользователя
define('PF_ADMIN', array(1, 2, 3, 4, 6, 7, 8, 9, 10)); // Для администратора ресурса

# Состояния в которых может находиться заказ
// 1 - ожидает подтверждения,
// 2 - укомплектован
// 3 - находится в пути
// 4 - выполнен
// 5 - отменен
define("ORDER_STATUS", array(1, 2, 3, 4, 5));

?>
