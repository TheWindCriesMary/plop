<?php
/**
 * Created by PhpStorm.
 * User: nadan
 * Date: 15/07/2017
 * Time: 00:28
 */


namespace AppBundle\CCP;

class CCPConfig
{

    public static $loginURL = "https://login.eveonline.com/oauth/authorize/";

    public static $clientID =  "6816a73eb73e4d4aac7c1e84de7af3f1";

    public static $secretKEY =  "oZ0KlU2CAAEqxncHpSgUoF1jr8ovsWEkOwOcGzgi";

    public static $redirectUrl =  "http://localhost/app_dev.php/ccpcallback";

    public static $datasource = 'tranquility';

    public static $verifyURL = 'https://login.eveonline.com/oauth/verify';

    public static $tokenURL = 'https://login.eveonline.com/oauth/token';
}