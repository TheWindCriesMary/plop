<?php


namespace AppBundle\Util;

use AppBundle\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;


/**
 * Created by PhpStorm.
 * User: nadan
 * Date: 28/07/2017
 * Time: 20:36
 */
class Core
{


    public static function getDefaultParameter(Registry $doctrine, Request $request){


        $parameters = array();

        if(UserUtil::isConnected($request)){
            $rep = $doctrine->getRepository(User::class);

            $user = $rep->findOneByCharId($request->getSession()->get('char_id'));


            $parameters['user'] = $user;
        }

        return $parameters;


    }







}