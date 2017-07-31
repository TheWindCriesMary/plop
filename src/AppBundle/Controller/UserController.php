<?php
/**
 * Created by PhpStorm.
 * User: nadan
 * Date: 29/07/2017
 * Time: 22:29
 */

namespace AppBundle\Controller;


use AppBundle\CCP\CCPUtil;
use AppBundle\CCP\TokenData;
use AppBundle\Entity\CharApi;
use AppBundle\Entity\User;
use AppBundle\Util\UserUtil;
use nullx27\ESI\Api\CharacterApi;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\CCP\CCPConfig;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use AppBundle\Util\Core;

class UserController extends Controller
{


    /**
     * This route de the profile page of a user
     *
     * @Route("/profile", name="profile")
     */
    public function profileAction(Request $request)
    {

        $parameters = Core::getDefaultParameter($this->getDoctrine(), $request);
        $parameters['base_dir'] = realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR;

        if(UserUtil::getUser($this->getDoctrine(), $request) == null)
            return $this->redirect('/');

        $session = $request->getSession();

        if(!$session)
            return $this->redirect('/');

        if ($session->get('token')){
            return $this->render('profile/index.html.twig', $parameters);
        }


        else {
            return $this->redirect('/');
        }
    }


    /**
     * List every api of a user. One api per character
     *
     * @Route("/profile/api", name="myapis")
     */
    public function myApiAction(Request $request){


        $parameters = Core::getDefaultParameter($this->getDoctrine(), $request);
        $parameters['base_dir'] = realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR;

        //TODO gestion si l'utilisateur n'est pas connecté

        $rep = $this->getDoctrine()->getRepository(CharApi::class);

        /**
         * @var $apis array(CharApi)
         */
        $apis = $rep->findByUser($parameters['user']->getId());


        foreach($apis as $api){

            $tokenData = new TokenData($api->getToken(), $api->getRefreshToken());
            if(CCPUtil::isTokenValid($tokenData)){
                $api->isValid = true;
            }
            else{
                $tokenData = CCPUtil::updateToken($tokenData);
                if( $tokenData == false){

                }
                else{
                    $api->isValid = true;

                    $api->setToken($tokenData->token);
                    $api->setRefreshToken($tokenData->refreshToken);
                    $this->getDoctrine()->getManager()->flush();

                }
            }
        }


        $parameters['apis'] = $apis;

        return $this->render('profile/apis.html.twig', $parameters);

    }
    /**
     * Basically generate the url to the ccp login page. This time with the scope needed.
     *
     * @Route("/profile/addapi", name="addapi")
     */
    public function addApiAction(Request $request)
    {
        $url = CCPConfig::$loginURL; //url de base de connection
        $url = $url . '?';
        $url = $url . 'response_type=code'; // le type de connection, on need un code pour generer un token aprés
        $url = $url . '&client_id=' . CCPConfig::$clientIDAPI;
        $url = $url . '&redirect_uri='.CCPConfig::$redirectUrlAPI ;
        $url = $url . '&scope=' . CCPConfig::$scopes;
        $url = $url . '&state=ajmzoeijapoziepaize'; //TODO random


        return $this->redirect($url);

    }

    /**
     * This route manage the redirection after login on ccp server, create a CharacterApi and add this to the database
     *
     * @Route("/profile/ccpcallback", name="ccpcallbackapi")
     */
    public function ccpCallBackApiAction(Request $request)
    {

        $user = UserUtil::getUser($this->getDoctrine(), $request);
        if(!$user) return $this->redirect('/');


        $userAgent = 'PLAP';

        $header = 'Authorization: Basic ' . base64_encode(CCPConfig::$clientIDAPI . ':' . CCPConfig::$secretKEYAPI);
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
            throw $this->createNotFoundException('Error from ccp. No response');
        }
        curl_close($ch);


        $response = json_decode($result, true);
        if (isset($response['error'])) {
            throw $this->createNotFoundException('Error from ccp. Error message : ' . $response['error']);
        }
        $access_token = $response['access_token'];
        $refresh_token = $response['refresh_token'];
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
            throw $this->createNotFoundException('Error from ccp. Can\'t get the character id');
        }
        /*if (strpos(@$response->Scopes, 'publicData') === false) {
            throw $this->createNotFoundException('Error from ccp. The scopes don\'t match');
            //TODO Test on the scopes
        }*/


        $charID = (int)$response->CharacterID;


        $doctrine = $this->getDoctrine();

        $esi = new CharacterApi();

        $charInfo = $esi->getCharactersCharacterId($charID);
        
        $api = new CharApi();
        $api->setCharId($charID)->setCharName($charInfo->getName())->setRefreshToken($refresh_token)->setToken($access_token)->setUser(UserUtil::getUser($this->getDoctrine(), $request));

        $doctrine->getManager()->persist($api);
        $doctrine->getManager()->flush();

        return $this->redirect('/profile/api');

    }

    /**
     * Remove an api
     *
     * @Route("/profile/removeapi/{id}", name="removeapi")
     */
    public function removeApiAction(Request $request, $id)
    {

        $user = UserUtil::getUser($this->getDoctrine(), $request);
        if($user == null) return $this->redirect('/');

        $rep = $this->getDoctrine()->getRepository(CharApi::class);
        $api = $rep->find($id);
        if( $api == null)  return $this->redirect('/profile/api');

        if($api->getUser()->getId() != $user->getId()) return $this->redirect('/');

        $em = $this->getDoctrine()->getManager();
        $em->remove($api);
        $em->flush();

        return $this->redirect('/profile/api');


    }




}