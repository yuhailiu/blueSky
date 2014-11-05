<?php
use Users\Tools\MyUtils;
$password = $_POST["password"] ? str_replace(" ", "", $_POST["password"]) : "";
if (MyUtils::isValidatePassword($password)) {
    $phoneNumber = $_POST["phoneNumber"];
    $phoneNumber = MyUtils::clearNumber($phoneNumber);
    if (MyUtils::isValidateTel($phoneNumber)) {
        $areaCode = $_POST["areaCode"];
        $areaCode = MyUtils::clearNumber($areaCode);
        $deviceCode = $_POST["deviceCode"];
        $deviceCode = str_replace(" ", "", $deviceCode);
        if (! $this->isAuthrizedUser($areaCode, $phoneNumber, $password) || ! MyUtils::isValidatePassword($password)) {
            $flag = "invalidUser";
        } else {
            $flag = "validUser";
        }
    } else {
        $flag = "phoneNumberError";
    }
} else {
    $flag = "invalidPassword";
}


