<?php

namespace AppBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Swagger\Client\Api\SearchApi;
use Swagger\Client\ApiClient;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use \Swagger\Client\Api\UniverseApi;

class DefaultController extends Controller
{
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
     * @Route("/test", name="test")
     */
    public function testAction(Request $request)
    {

        $api = new UniverseApi();
        $region = $api->getUniverseRegions();

        foreach ($region as $r){
            echo $r;
        }
        die('test');
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);
    }
}
