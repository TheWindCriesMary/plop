<?php
/**
 * Created by PhpStorm.
 * User: nadan
 * Date: 08/09/2017
 * Time: 17:35
 */

namespace AppBundle\Controller;

use AppBundle\Discord\DiscordConfig;
use Discord\Discord;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class DiscordController extends Controller
{



    /**
     *
     *
     * @Route("/discord/login", name="discordlogin")
     */
    public function discordLoginAction(Request $request)
    {

        $url = DiscordConfig::$loginURI;

        $url = $url . "&client_id=" . DiscordConfig::$cliendId;
        $url = $url . "&scope=bot";
        $url = $url . "&state=15773059ghq9183habn";
        $url = $url . "&permissions=2146959615";
        $url = $url . "&redirect_uri=" . DiscordConfig::$redirectURI;
        return $this->redirect($url);


    }




    /**
     *
     *
     * @Route("/discord/test", name="discordtest")
     */
    public function discordTestAction(Request $request)
    {

        $provider = new \Discord\OAuth\Discord([
            'clientId' => DiscordConfig::$cliendId,
            'clientSecret' => DiscordConfig::$cliendSecret,
            'redirectUri' => DiscordConfig::$redirectURI,
        ]);

        $url = $provider->getBaseAccessTokenUrl([]);

        echo $url;


        /*$uri = parse_url($url);

        echo  $uri['path'];*/

        /*
        $discord = new Discord([
            'token' => 'pdpRKYW7djHwLyIwzYhhxmKt2SxxVX',
        ]);

        /*
        $discord->on('ready', function ($discord) {
            echo "Bot is ready.", PHP_EOL;

            // Listen for events here
            $discord->on('message', function ($message) {
                echo "Recieved a message from {$message->author->username}: {$message->content}", PHP_EOL;
            });
        });*/



    }

    /**
     *
     *
     * @Route("/discord/redirect", name="discordredirect")
     */
    public function discordRedirectAction(Request $request)
    {


        //Getting a token and refresh from ccp with the code-----------------------------------
        $header = 'Authorization: Basic ' . base64_encode(DiscordConfig::$cliendId . ':' . DiscordConfig::$cliendSecret);
        $fields_string = '';
        $fields = array(
            'grant_type' => 'authorization_code',
            'code' => $_GET['code'],
        );
        foreach ($fields as $key => $value) {
            $fields_string .= $key . '=' . $value . '&';
        }
        rtrim($fields_string, '&');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, DiscordConfig::$redirectURI);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array($header));
        curl_setopt($ch, CURLOPT_POST, count($fields));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        $result = curl_exec($ch);
        if ($result === false) {
            throw $this->createNotFoundException('Error from discord.');
        }
        curl_close($ch);
        echo $result;
        $response = json_decode($result, true);


        if (isset($response['error'])) {
            throw $this->createNotFoundException('Error from ccp. Error message : ' . $response['error']);
        }


        $access_token = $response['access_token'];
        $refresh_token = $response['refresh_token'];

       // echo 'access_token : ' .  $access_token . ' , refresh_token : ' . $refresh_token;
        //-----------------------------------------------------------------------------------------


        die('discord redirect');

    }
}