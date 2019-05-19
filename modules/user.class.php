<?php

class User {
  private $id;
  private $firstname;
  private $lastname;
  private $gender;
  private $phone_number;
  private $email;
  private $zip_code;
  private $state;
  private $city;
  private $address;
  private $level;

  public function __construct($method, $data) { // Создает пользователя из массива данных о нем или исходя из его ID
    if ($method == "id") {
      $db = DB::getInstance();
      $data = $db->query("SELECT * FROM ".DB_TABLES["user"]." WHERE id=".$data." LIMIT 1");
      if (gettype($data) == "boolean" || $data->num_rows != 1) {
        $this->id = false;
        return null;
      }
      $data = $data->fetch_assoc();
    }

    $this->id = $data["id"];
    $this->firstname = $data["firstname"];
    $this->lastname = $data["lastname"];
    $this->gender = $data["gender"];
    $this->phone_number = $data["phone_number"];
    $this->email = $data["email"];
    $this->zip_code = $data["zip_code"];
    $this->state = $data["state"];
    $this->city = $data["city"];
    $this->address = $data["address"];
    $this->level = $data["level"];
  }
  public function getId() {
    return $this->id;
  }
  public function getFirstName() {
    return $this->firstname;
  }
  public function getLastName() {
    return $this->lastname;
  }
  public function getGenderCode() {
    return $this->gender;
  }
  public function getGenderName() {
    return $this->gender == 1 ? "Мужчина" : "Женщина";
  }
  public function getPhoneNumber() {
    return $this->phone_number;
  }
  public function getEmail() {
    return $this->email;
  }
  public function getZipCode() {
    return $this->zip_code;
  }
  public function getState() {
    return $this->state;
  }
  public function getCity() {
    return $this->city;
  }
  public function geAddress() {
    return $this->address;
  }
  public function getLevel() {
    return $this->level;
  }
  public function getDataArr() {
    return array("id"           => $this->id,
                 "firstname"    => $this->firstname,
                 "lastname"     => $this->lastname,
                 "gender"       => $this->gender,
                 "phone_number" => $this->phone_number,
                 "email"        => $this->email,
                 "zip_code"     => $this->zip_code,
                 "state"        => $this->state,
                 "city"         => $this->city,
                 "address"      => $this->address,
                 "level"        => $this->level);
  }
  public function setFirstName($value) {
    $db = DB::getInstance();
    return $db->query("UPDATE ".DB_TABLES["user"]." SET firstname='".$value."' WHERE id=".$this->id);
  }
  public function setLastName($value) {
    $db = DB::getInstance();
    return $db->query("UPDATE ".DB_TABLES["user"]." SET lastname='".$value."' WHERE id=".$this->id);
  }
  public function setGender($value) {
    $db = DB::getInstance();
    return $db->query("UPDATE ".DB_TABLES["user"]." SET gender='".$value."' WHERE id=".$this->id);
  }
  public function setPhone($value) {
    $db = DB::getInstance();
    return $db->query("UPDATE ".DB_TABLES["user"]." SET phone_number='".$value."' WHERE id=".$this->id);
  }
  public function setEmail($value) {
    $db = DB::getInstance();
    return $db->query("UPDATE ".DB_TABLES["user"]." SET email='".$value."' WHERE id=".$this->id);
  }
  public function setZipCode($value) {
    $db = DB::getInstance();
    return $db->query("UPDATE ".DB_TABLES["user"]." SET zip_code='".$value."' WHERE id=".$this->id);
  }
  public function setState($value) {
    $db = DB::getInstance();
    return $db->query("UPDATE ".DB_TABLES["user"]." SET state='".$value."' WHERE id=".$this->id);
  }
  public function setCity($value) {
    $db = DB::getInstance();
    return $db->query("UPDATE ".DB_TABLES["user"]." SET city='".$value."' WHERE id=".$this->id);
  }
  public function setAddress($value) {
    $db = DB::getInstance();
    return $db->query("UPDATE ".DB_TABLES["user"]." SET address='".$value."' WHERE id=".$this->id);
  }
  public function setLevel($value) {
    $db = DB::getInstance();
    return $db->query("UPDATE ".DB_TABLES["user"]." SET level='".$value."' WHERE id=".$this->id);
  }
  public function setData($data) {
    $data["id"] = $this->getId();
    $data["level"] = $this->getLevel();
    if (self::edit($data)) {
      Auth::editCurrentUser($data);
      return true;
    }
    return false;
  }
  /* Статические методы для управления пользователями */
  public static function add($data) {
    $db = DB::getInstance();

    require_once(MODULES_DIR."cryptor.class.php");
    $data["password"] = Cryptor::encryptText($data["password"]);

    return $db->query("INSERT INTO ".DB_TABLES["user"]."(firstname, lastname, gender, phone_number, email, password, level".
                       ((isset($data["state"]) && !empty($data["state"])) ? ", state, city, address, zip_code) " : ") ").
                       "VALUES('${data["firstname"]}', '${data["lastname"]}', '${data["gender"]}', ".
                              "'${data["phone_number"]}', '${data["email"]}', '${data["password"]}', '${data["level"]}'".
                        ((isset($data["state"]) && !empty($data["state"])) ?
                        ", '${data["state"]}', '${data["city"]}', '${data["address"]}', '${data["zip_code"]}')" : ")"));
  }
  public static function edit($data) { // Изменяет информацию о конкретном пользователе
    $db = DB::getInstance();

    if (strlen($password) != 0) {
      require_once(MODULES_DIR."cryptor.class.php");
      $data["password"] = Cryptor::encryptText($data["password"]);
    }

    return $db->query("UPDATE ".DB_TABLES["user"]." SET firstname='".$data["firstname"]."', ".
                                                       "lastname='".$data["lastname"]."', ".
                                                       "gender='".$data["gender"]."', ".
                                                       "phone_number='".$data["phone_number"]."', ".
                                                       "email='".$data["email"]."', ".
                                                       "zip_code='".$data["zip_code"]."', ".
                                                       "state='".$data["state"]."', ".
                                                       "city='".$data["city"]."', ".
                                                       "address='".$data["address"]."', ".
                                                       (!empty($data["password"]) ? "password='".$data["password"]."', " : "")
                                                       ."level='".$data["level"]."' ".
                                                    "WHERE id='".$data["id"]."' LIMIT 1");
  }
  public static function remove($id) { // Удаляет конкретного пользователя
    $db = DB::getInstance();
    return $db->query("DELETE FROM ".DB_TABLES["user"]." WHERE id='".$id."' LIMIT 1");
  }
  public static function clear() { // Удаляет всех пользователей
    $db = DB::getInstance();
    return $db->query("DELETE FROM ".DB_TABLES["user"]);
  }
  /* static functions */
  public static function getUserInformation($user_id) {
    $db = DB::getInstance();

    $user_info = $db->query("SELECT id, firstname, lastname, gender, phone_number, email, ".
                                   "zip_code, state, city, address, level FROM ".DB_TABLES["user"].
                            " WHERE id='".$user_id."' LIMIT 1");
    if (!DB::checkDBResult($user_info)) return false;

    return $user_info->fetch_assoc();
  }
}

?>
