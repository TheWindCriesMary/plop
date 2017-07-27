<?php
/**
 * Created by PhpStorm.
 * User: nadan
 * Date: 27/07/2017
 * Time: 03:10
 */

namespace AppBundle\CCP;


use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CCPUtil
{




    public static function isTokenValid(SessionInterface $session) {

        //TODo Check for session

        $userAgent = 'PLAP';

        $access_token = $session->get('token');
        $ch = curl_init();
        // Get the Character details from SSO
        $header = 'Authorization: Bearer ' . $access_token;
        curl_setopt($ch, CURLOPT_URL, CCPConfig::$verifyURL);
        curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array($header));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        $result = curl_exec($ch);
        if ($result === false) {
            //TODO erreur management
        }

        curl_close($ch);

        $response = json_decode($result);
        return isset($response->CharacterID); //si on a le charactere id alors le token est encore valid, sinon il n'est plus valid



    }

    public static function updateToken(SessionInterface $session){

        $userAgent = 'PLAP';



        $header = 'Authorization: Basic ' . base64_encode(CCPConfig::$clientID . ':' . CCPConfig::$secretKEY);
        $fields_string = '';
        $fields = array(
            'grant_type' => 'refresh_token',
            'refresh_token' => $session->get('refresh_token'),
        );
        foreach ($fields as $key => $value) {
            $fields_string .= $key . '=' . $value . '&';
        }
        rtrim($fields_string, '&');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, CCPConfig::$tokenURL);
        curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array($header));
        curl_setopt($ch, CURLOPT_POST, count($fields));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        $result = curl_exec($ch);
        if ($result === false) {
            //TODO erreur management
        }

        curl_close($ch);
        $response = json_decode($result, true);
        if (isset($response['error'])) {
            die("\nccpCallBack CCP Error");
        }
        $access_token = $response['access_token'];
        $refresh_token = $response['refresh_token'];

        /*echo 'refreshing token <br>';
        echo 'token : ' . $access_token;
        echo 'refresh_token : ' . $refresh_token;*/
    }

}