<?php
namespace Users\Model;

use Users\Tools\MyUtils;
use Zend\Validator\EmailAddress;
use Zend\Validator\Date;

class Target
{
    public $target_name;
    public $target_creater;
    public $target_end_time;
    public $target_lastModify_time;
    public $target_content;
    public $target_status;
    public $target_id;
    public $parent_target_id;
    public $target_create_time;
    public $receiver;
    public $eventIdentifier;
    

    public function isValidate()
    {
        $flag = true;
        if ((int) $this->target_id) {
            if ((int) $this->parent_target_id >= 0) {
                if (MyUtils::isValidateName($this->target_name)) {
                    $emailValidation = new EmailAddress();
                    if ($emailValidation->isValid($this->target_creater) && $emailValidation->isValid($this->receiver)) {
                        $dateValidation = new Date();
                        $now = time();
                        $end = mktime($this->target_end_time);
                        if ($dateValidation->isValid($this->target_end_time) && $end > $now) {
                            if (MyUtils::isValidateContent($this->target_content)) {
                                if (MyUtils::isValidateStatus($this->target_status)) {
                                    // validate a target
                                    $flag = true;
                                } else {
                                    // invalidate status
                                    $flag = false;
                                    MyUtils::writelog("invalidate target status");
                                }
                            } else {
                                // invalidate content
                                $flag = false;
                                MyUtils::writelog("invalidate target content");
                            }
                        } else {
                            // invalidate target date
                            $flag = false;
                            MyUtils::writelog("invalidate targaet date");
                        }
                    } else {
                        // invalidate target_creater and target_receiver
                        $flag = false;
                        MyUtils::writelog("invalidate creater and receiver");
                    }
                } else {
                    // invalidate target_name
                    $flag = false;
                    MyUtils::writelog("invalidate target_name");
                }
            } else {
                // invalidate parent_id
                $flag = false;
                MyUtils::writelog("invalidate parent id");
            }
        } else {
            // invalidate target id
            $flag = false;
            MyUtils::writelog("invalidate target id");
        }
        return $flag;
    }
}
