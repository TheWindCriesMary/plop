<?php

namespace AppBundle\Controller;

use AppBundle\CCP\CCPUtil;
use AppBundle\Util\Core;
use AppBundle\Util\UserUtil;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use AppBundle\CCP\CCPConfig;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class DefaultController extends Controller
{



    /**
     * This route manage the redirection after login on ccp server, create the session and user
     *
     * @Route("/ccpcallback", name="ccpCallBack")
     */
    public function ccpCallBackAction(Request $request)
    {


        $userAgent = 'PLAP';


        //Getting a token and refresh from ccp with the code-----------------------------------
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
            throw $this->createNotFoundException('Error from ccp.');
        }
        curl_close($ch);

        $response = json_decode($result, true);
        if (isset($response['error'])) {
            throw $this->createNotFoundException('Error from ccp. Error message : ' . $response['error']);
        }
        $access_token = $response['access_token'];
        $refresh_token = $response['refresh_token'];
        //-----------------------------------------------------------------------------------------


        //getting the char id from ccp and testing if the token is good----------------------------
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
            throw $this->createNotFoundException('Error from ccp. (no response)');
        }
        curl_close($ch);
        $response = json_decode($result);
        if (!isset($response->CharacterID)) {
            throw $this->createNotFoundException('Error from ccp. Can\'t get the character id'); //when we don't have the charId, it's probably because it failed
        }
        if (strpos(@$response->Scopes, 'publicData') === false) {
            throw $this->createNotFoundException('Error from ccp. The scopes don\'t match');
        }

        $charID = (int)$response->CharacterID;
        //-----------------------------------------------------------------------------------------


        //setting up the session
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

        //creating the user in the database if necessary
        if (UserUtil::userExist($session, $doctrine)) {
        }
        else
        {
            UserUtil::addUser($session, $doctrine);

        }


        return $this->redirect('profile');
    }

    /**
     * this route logout the user
     *
     * @Route("/logout", name="logout")
     */
    public function logoutAction(Request $request)
    {

        $parameters = Core::getDefaultParameter($this->getDoctrine(), $request);
        $parameters['base_dir'] = realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR;

        if($request->getSession()){
            $request->getSession()->clear();
            $request->getSession()->invalidate(0);
        }
        //

        return $this->redirect('/');
    }


    /**
     *
     * This route is the homepage idiot
     *
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {

        $parameters = Core::getDefaultParameter($this->getDoctrine(), $request);
        $parameters['base_dir'] = realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR;



        return $this->render('default/index.html.twig', $parameters);
        //
    }





    /**
     *
     * Do your test here.
     *
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
            CCPUtil::updateSessionToken($session);
            die('<br> the end');

        }


        else {
            die('no token in session');
        }





        die('test');
    }


    /**
     *
     * Basically generate the url to the ccp login page.
     *
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
}
