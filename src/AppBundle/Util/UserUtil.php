<?php
/**
 * Created by PhpStorm.
 * User: nadan
 * Date: 27/07/2017
 * Time: 16:30
 */

namespace AppBundle\Util;


use AppBundle\CCP\CCPConfig;
use AppBundle\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Registry;
use nullx27\ESI\Api\CharacterApi;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use AppBundle\CCP\CCPUtil;

class UserUtil
{



    private static $user = null;

    public static function userExist(SessionInterface $session, Registry $doctrine){
        $repository = $doctrine->getRepository(User::class);

        $user = $repository->findOneBy(array('charId' => $session->get('char_id')));

        if ($user) return true;

        return false;


    }

    /**
     ** @return User
     */
    public static function getUser(Registry $doctrine, Request $request){
        if(UserUtil::$user!=null)return UserUtil::$user;

        $rep = $doctrine->getRepository(User::class);

        UserUtil::$user = $rep->findOneByCharId($request->getSession()->get('char_id'));

        return UserUtil::$user;

    }


    public static function addUser(SessionInterface $session, Registry $doctrine){


         $api = new CharacterApi();


        $charInfo = $api->getCharactersCharacterId($session->get('char_id'), CCPConfig::$datasource);



        $group = 1;

        if ($charInfo->getCorporationId() == Util::$corpId) $group = 2;

        $user = new User();

        $user->setCharId($session->get('char_id'));
        $user->setCorpId($charInfo->getCorporationId());
        $user->setGroupId($group);
        $user->setName($charInfo->getName());

        $em = $doctrine->getManager();

        $em->persist($user);
        $em->flush();


    }


    public static function isConnected(Request $request){
        //si il n'y a pas de session l'utilisateur ne sais jamais connecté sur le site
        $session = $request->getSession();
        if (!$session) {
            return false;
        }



        //Si il n'y a pas de token et de refresh token, l'utilisateur est deconnecter
        if(!($session->has('token') and $session->has('refresh_token'))){
            return false;
        }

        if(CCPUtil::isSessionTokenValid($session)){
            return true;
        }
        else{
            if(CCPUtil::updateSessionToken($session)){
                return true;
            }


            else{
                $request->getSession()->clear();
                $request->getSession()->invalidate(0);
                return false;

            }
        }
    }

}