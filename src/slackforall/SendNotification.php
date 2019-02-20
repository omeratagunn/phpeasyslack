<?php
namespace Slackforall;

trait SendNotification
{
    private static $params =
        [
            'TEXT' => null,
            'CHANNEL' => null,
            'DATE' => null
        ];


    private static $env;

    private static $error = array();
    /**
     * @return int
     * To detect your operating system and do the job based on it.
     *
     * Windows or others. Do not forget to have curl in your linux system.
     */
    protected static function setup(){

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {

            if(!extension_loaded('curl')){

                self::$error[ 'message' ] = 'Curl extension must be enabled';

                self::$error[ 'status' ] = 1;

            }

            return self::$env = 1;
        }

        else {

            return self::$env = 0;

        }


    }

    /**
     * @param string $webhook
     */
    protected static function checkLink(string $webhook){

        $data = explode("/",$webhook);

        if(count($data) != 7){

            self::$error[ 'message' ] = "This is not a valid slack webhook link";
            self::$error[ 'status' ] = 1;

        }
        if($data[0] != "https:"){

            self::$error[ 'message' ] = "Check the link you had entered";
            self::$error[ 'status' ] = 1;
        }
        if($data[2] != "hooks.slack.com"){

            self::$error[ 'message' ] = "Link must be hooks.slack.com";
            self::$error[ 'status' ] = 1;
        }

    }
    /**
     * @param $text
     * @param $webhook
     * @param bool $adddate
     * @return bool|\Exception
     */
    public static function send(string $text, string $webhook, $adddate = false){

        self::$params['TEXT'] = $text;

        self::$params['CHANNEL'] = $webhook;

        self::checkLink($webhook);

        self::setup();

        if(!empty(self::$error)){

            return self::$error;

        }

        if($adddate === true) {

            self::$params['DATE'] = ' - '.date("Y-m-d G:i");

        }

        if(self::$env == 0) {

            try {

                shell_exec('curl -X POST -H \'Content-type: application/json\' --data \'{"text":"' . self::$params['TEXT'] . '"}\' ' . self::$params['CHANNEL']);

                return true;

            }
            catch (\Exception $e){

                return $e->getMessage();

            }
        }

        else
        {

            try {

                $message = array('payload' => json_encode(array('text' => self::$params['TEXT'].self::$params['DATE'])));

                $c = curl_init(self::$params['CHANNEL']);

                curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);

                curl_setopt($c, CURLOPT_TIMEOUT_MS, 20000);

                curl_setopt($c, CURLOPT_POST, true);

                curl_setopt($c, CURLOPT_POSTFIELDS, $message);

                curl_exec($c);

                curl_close($c);

                return true;

            }

            catch (\Exception $e)
            {

                return $e->getMessage();

            }
        }

    }



}
