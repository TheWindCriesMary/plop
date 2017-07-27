<?php

namespace AppBundle\Controller;

use AppBundle\CCP\CCPUtil;
use AppBundle\Util\UserUtil;
use nullx27\ESI\Api\CharacterApi;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use AppBundle\Entity\LoginCcpCallBack;
use AppBundle\CCP\CCPConfig;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class DefaultController extends Controller
{



    /**
     * @Route("/ccpcallback", name="ccpCallBack")
     */
    public function ccpCallBackAction(Request $request)
    {


        $userAgent = 'PLAP';

        $url = 'https://login.eveonline.com/oauth/token';
        $verify_url = 'https://login.eveonline.com/oauth/verify';

        $header = 'Authorization: Basic ' . base64_encode(CCPConfig::$clientID . ':' . CCPConfig::$secretKEY);
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
        curl_setopt($ch, CURLOPT_URL, $url);
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
        $ch = curl_init();
        // Get the Character details from SSO
        $header = 'Authorization: Bearer ' . $access_token;
        curl_setopt($ch, CURLOPT_URL, $verify_url);
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
        if (!isset($response->CharacterID)) {
            //TODO erreur management
        }
        if (strpos(@$response->Scopes, 'publicData') === false) {
            //TODO erreur management
        }


        $charID = (int)$response->CharacterID;


        $session = $request->getSession();
        if (!$session) {
            $session = new Session();
            $session->start();
        }


        //TODO char name
        $session->set('token', $access_token);
        $session->set('refresh_token', $refresh_token);
        $session->set('char_id', $charID);


        $doctrine = $this->getDoctrine();

        if (UserUtil::userExist($session, $doctrine)) {
            echo 'user exist <br>';
        }
        else
        {
            echo 'user not exist <br>';
            UserUtil::addUser($session, $doctrine);

        }


        /*echo 'token : ' . $access_token . '<br>';
        echo 'refresh_token : ' . $refresh_token . '<br>';
        echo 'char_id : ' . $charID . '<br>';*/

        //die('lel');
        return $this->redirect('profile');
    }


    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);
    }

    /**
     * @Route("/profile", name="profile")
     */
    public function profileAction(Request $request)
    {

        $session = $request->getSession();

        if(!$session)
            return $this->redirect('index');

        if ($session->get('token')){

            $apiChar = new CharacterApi();


            $charInfo = $apiChar->getCharactersCharacterId($session->get('char_id'), CCPConfig::$datasource);
            echo 'hello ' . $charInfo->getName();


            die('<br> the end');

        }


        else {
            return $this->redirect('index');
        }


        // replace this example code with whatever you need
        /*return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);*/
    }

    /**
     * @Route("/test", name="test")
     */
    public function testAction(Request $request)
    {

        $session = $request->getSession();

        if(!$session)
            die('no session');

        if ($session->get('token')){



            echo $session->get('token'). "<br>" ;
            echo $session->get('refresh_token'). "<br>" ;
            echo $session->get('char_id'). "<br>" ;

            //if(!CCPUtil::isTokenValid($session)) CCPUtil::updateToken($session);
            CCPUtil::updateToken($session);
            die('<br> the end');

        }


        else {
            die('no token in session');
        }





        die('test');
       /* return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);*/
    }


    /**
     * @Route("/login", name="login")
     */
    public function loginAction(Request $request)
    {
        $url = CCPConfig::$loginURL; //url de base de connection
        $url = $url . '?';
        $url = $url . 'response_type=code'; // le type de connection, on need un code pour generer un token aprés
        $url = $url . '&client_id=' . CCPConfig::$clientID;
        $url = $url . '&redirect_uri='.CCPConfig::$redirectUrl ;
        $url = $url . '&scope=publicData'; //no scope needded -- publicData+esi-wallet.read_character_wallet.v1
        $url = $url . '&state=ajmzoeijapoziepaize'; //TODO random


        return $this->redirect($url);


    }




    /**
     * @Route("/testDB", name="testDB")
     */
    public function testDBAction(Request $request)
    {

        //random string generator
        //TODO move it
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < 128; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        //------

        $loginCCP = new LoginCcpCallBack();
        $loginCCP->setState($randomString);
        $loginCCP->setTime(new \DateTime('2017-07-16 14:00:00'));
        $em = $this->getDoctrine()->getManager();
        $em->persist($loginCCP);
        $em->flush();


        die('testDB');
        /*return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);*/
    }
}
