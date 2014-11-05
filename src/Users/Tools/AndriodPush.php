<?php
namespace Users\Tools;

require_once '../jPush/vendor/autoload.php';

use JPush\Model as M;
use JPush\JPushClient;
use JPush\JPushLog;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use JPush\Exception\APIConnectionException;
use JPush\Exception\APIRequestException;

class AndriodPush
{

    public function easyPush($target, $pushStack)
    {
        date_default_timezone_set(PRC);
        $br = '<br/>';
        $spilt = ' - ';
        
        $master_secret = '0cb5b38dc0ce573521a41fa9';
        $app_key = '90026cbc84b7c60e2217f601';
        JPushLog::setLogHandlers(array(
            new StreamHandler('jpush.log', Logger::DEBUG)
        ));
        $client = new JPushClient($app_key, $master_secret);
        
        // easy push
        try {
            $extras = array(
                "userStatus" => $pushStack->userStatus,
                "target" => $target,
                "notificationNumber" => $pushStack->notificationNumber
            );
            $notification = array(
                "alert" => $pushStack->message,
                "title" => "ReadyGo",
                "extras" => $extras
            );
            // $body = array(
            // 'platform' => 'all',
            // 'audience' => 'all',
            // 'notification' => $notification
            // );
            // $payload = json_encode($body);
            // $result = $client->push()->send($payload);
            $registration_ids = array();
            array_push($registration_ids, $pushStack->deviceToken);
            $result = $client->push()
                ->setPlatform(M\all)
                ->setAudience(M\audience(M\registration_id($registration_ids)))
                ->setNotification($notification)
                ->send();
//             echo 'Push Success.' . $br;
//             echo 'sendno : ' . $result->sendno . $br;
//             echo 'msg_id : ' . $result->msg_id . $br;
//             echo 'Response JSON : ' . $result->json . $br;
        } catch (APIRequestException $e) {
            echo 'Push Fail.' . $br;
            echo 'Http Code : ' . $e->httpCode . $br;
            echo 'code : ' . $e->code . $br;
            echo 'Error Message : ' . $e->message . $br;
            echo 'Response JSON : ' . $e->json . $br;
            echo 'rateLimitLimit : ' . $e->rateLimitLimit . $br;
            echo 'rateLimitRemaining : ' . $e->rateLimitRemaining . $br;
            echo 'rateLimitReset : ' . $e->rateLimitReset . $br;
        } catch (APIConnectionException $e) {
            echo 'Push Fail: ' . $br;
            echo 'Error Message: ' . $e->getMessage() . $br;
            // response timeout means your request has probably be received by JPUsh Server,please check that whether need to be pushed again.
            echo 'IsResponseTimeout: ' . $e->isResponseTimeout . $br;
        }
        
//         echo $br . '-------------' . $br;
    }
}

