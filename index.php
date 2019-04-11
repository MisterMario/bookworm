<?php

require_once("config.php");
require_once(MODULES_DIR."review.class.php");
require_once(MODULES_DIR."order.class.php");

session_start();
$user = Auth::createUser();

if (count($_POST) > 0) require_once("post.php"); // Обработкой POST займусь позднее

$page_info = SEF::getPageInfo($_SERVER["REQUEST_URI"]);

// Проверка того, что страница существует и доступна пользователю
if (!$page_info["page_code"])
  $page_info["page_code"] = 404; // Если страницы не существует
elseif ($user) {
  if ($user->getLevel() == 2 && !in_array($page_info["page_code"], PF_SIMPLE_USER))
    $page_info["page_code"] = 403;
  elseif ($user->getLevel() == 3 && !in_array($page_info["page_code"], PF_ADMIN))
    $page_info["page_code"] = 403;
} elseif (!in_array($page_info["page_code"], PF_NO_USER))
  $page_info["page_code"] = 403;

$page = new PageBuilder\Page($page_info, $user);

echo $page->getHTML();

?>