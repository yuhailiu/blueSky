<?php
/**
 * login failed times
 * @var int
 */
define(FAILED_TIMES, 9); // login failed times
//share, agree, creater-reject
$createrAllow_mainWithout = array(1, 2, 7, 10);
//achieve, fail
$createrAllow_mainWith = array(5,6);
//modify, receiver-reject, apply-achieve, apply-fail
$receiverAllow = array(3,4,7,8,9);