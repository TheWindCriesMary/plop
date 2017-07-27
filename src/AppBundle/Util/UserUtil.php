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

class UserUtil
{


    public static function userExist(SessionInterface $session, Registry $doctrine){
        $repository = $doctrine->getRepository(User::class);

        $user = $repository->findOneBy(array('charId' => $session->get('char_id')));

        if ($user) return true;

        return false;


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

}