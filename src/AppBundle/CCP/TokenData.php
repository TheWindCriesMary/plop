<?php
/**
 * Created by PhpStorm.
 * User: nadan
 * Date: 30/07/2017
 * Time: 23:15
 */

namespace AppBundle\CCP;


class TokenData
{

    public $token;
    public $refreshToken;

    public function __construct($token, $refreshToken)
    {
        $this->token = $token;
        $this->refreshToken = $refreshToken;

    }

}