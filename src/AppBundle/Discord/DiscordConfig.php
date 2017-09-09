<?php
/**
 * Created by PhpStorm.
 * User: nadan
 * Date: 08/09/2017
 * Time: 17:33
 */

namespace AppBundle\Discord;

class DiscordConfig
{

    public static $cliendId = '355730171489157133';

    public static $cliendSecret = 'unTgDpJ5rtlYw60MfxhfvssNwRmlSHCy';

    public static $redirectURI = "http://localhost/app_dev.php/discord/redirect";

    public static $loginURI = "https://discordapp.com/oauth2/authorize?response_type=code";


}