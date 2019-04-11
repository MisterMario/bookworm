<?php

class Customer {
  public static function add($order_id, $customer) {
    $db = DB::getInstance();

    if (!Order::existsOrder($order_id)) return false;
    return $db->query("INSERT INTO ".DB_TABLES["customer"].
                                 "(order_id, firstname, lastname, phone_number, email, zip_code, state, city, address) ".
                                 "VALUES('".$order_id."', ".
                                        "'".$customer["firstname"]."', ".
                                        "'".$customer["lastname"]."', ".
                                        "'".$customer["phone_number"]."', ".
                                        "'".$customer["email"]."', ".
                                        "'".$customer["zip_code"]."', ".
                                        "'".$customer["state"]."', ".
                                        "'".$customer["city"]."', ".
                                        "'".$customer["address"]."')");
  }

  public static function edit($customer) {
    $db = DB::getInstance();

    if (!Order::existsOrder($customer["order_id"])) return false;
    return $db->query("UPDATE ".DB_TABLES["cutomer"]." SET ".
                                               "order_id='".$customer["order_id"]."', ".
                                               "firstname='".$customer["firstname"]."', ".
                                               "lastname='".$customer["lastname"]."', ".
                                               "phone_number='".$customer["phone_number"]."', ".
                                               "email='".$customer["email"]."', ".
                                               "zip_code='".$customer["zip_code"]."', ".
                                               "state='".$customer["state"]."', ".
                                               "address='".$customer["address"]."' ".
                                               "WHERE id='".$customer["id"]."' LIMIT 1");
  }

  public static function remove($customer_id) {
    $db = DB::getIntance();
    return $db->query("DELETE FROM ".DB_TABLES["customer"]." WHERE id='".$customer_id."' LIMIT 1");
  }

  public static function removeAll() {
    $db = DB::getIntance();
    return $db->query("TRUNCATE TABLE ".DB_TABLES["customer"]);
  }

  public static function existsCustomer($customer_id) {
    $db = DB::getIntance();

    $cutomer_exsits = $db->query("SELECT order_id FROM ".DB_TABLES["customer"]." WHERE id='".$customer_id."' LIMIT 1");
    return DB::checkDBResult($cutomer_exsits);
  }
}

 ?>
