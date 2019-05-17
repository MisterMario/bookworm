<?php

$data = json_decode(file_get_contents("php://input"));

$answer = array("status" => true, "message" => "Ошибка при записи файла!");
$answer["status"] = file_put_contents("image.png", base64_decode($data["image"]));

echo json_encode($answer);

 ?>
