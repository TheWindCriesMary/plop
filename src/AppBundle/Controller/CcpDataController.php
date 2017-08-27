<?php
/**
 * Created by PhpStorm.
 * User: nadan
 * Date: 02/08/2017
 * Time: 19:39
 */

namespace AppBundle\Controller;


use AppBundle\CCP\CCPConfig;
use AppBundle\Entity\Category;
use AppBundle\Entity\Item;
use AppBundle\Entity\ItemGroup;
use nullx27\ESI\Api\UniverseApi;
use nullx27\ESI\ApiException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class CcpDataController extends Controller
{

    /**
     * Remove a group
     *
     * @Route("/admin/ccpdata", name="ccpdata")
     */
    public function ccpdataAction(Request $request)
    {

        die('ccpdata');


    }

    /**
     * Get categories and groups
     *
     * @Route("/admin/ccpdata/update", name="ccpdataupdate")
     */
    public function ccpdataUpdateAction(Request $request)
    {

        set_time_limit(0);

        $api = new UniverseApi();
        $em = $this->getDoctrine()->getManager();

        //category---------------------------------------------------------------------------------
        $cat = $api->getUniverseCategories(CCPConfig::$datasource);
        $catRep = $this->getDoctrine()->getRepository(Category::class);




        foreach ($cat as $c) {

            $catData = $api->getUniverseCategoriesCategoryId($c, CCPConfig::$datasource, 'en-us');
            if($catData->getPublished()){
                echo $c . ', Name :' . $catData->getName() . '<br>';


                $category = $catRep->find($c);

                if (!$category) {
                    $category = new Category();
                    $category->setId($catData->getCategoryId())->setName($catData->getName());
                    $em->persist($category);
                } else {
                    $category->setName($catData->getName());
                }
                $em->flush();
            }
        }
        //-----------------------------------------------------------------------------------------

        //group------------------------------------------------------------------------------------


        $groupRep = $this->getDoctrine()->getRepository(ItemGroup::class);

        $group = array();
        $page = 1;
        do{
            $groupTmp = $api->getUniverseGroups(CCPConfig::$datasource, $page);
            $group = array_merge($group, $groupTmp);
            $page++;

        }while(count($groupTmp)>0);

        foreach ($group as $g){

            $groupData = $api->getUniverseGroupsGroupId($g, CCPConfig::$datasource, 'en-us');



            if($groupData->getPublished()){
                if($groupData->getPublished()){
                    echo $g . ', Name :' . $groupData->getName() . '<br>';


                    $groupDB = $groupRep->find($g);

                    if (!$groupDB) {
                        $groupDB = new ItemGroup();
                        $groupDB->setId($g)->setName($groupData->getName())->setCategory($catRep->find($groupData->getCategoryId()));
                        $em->persist($groupDB);
                    } else {
                        $groupDB->setName($groupData->getName());
                        $groupDB->setCategory($catRep->find($groupData->getCategoryId()));
                    }
                    $em->flush();
                }
            }

        }

        //-----------------------------------------------------------------------------------------

        die('ccpdata');


    }
    /**
     * Get item from the esi
     *
     * @Route("/admin/ccpdata/updateitem", name="ccpdataupdateitem")
     */
    public function ccpdataUpdateItemAction(Request $request)
    {

        set_time_limit(0);

        $api = new UniverseApi();
        $em = $this->getDoctrine()->getManager();


        $itemRep = $this->getDoctrine()->getRepository(Item::class);
        $groupRep = $this->getDoctrine()->getRepository(ItemGroup::class);

        $itemsIds = array();
        $page = 3;
        do{
            $itemsIdsTmp = $api->getUniverseTypes(CCPConfig::$datasource, $page);
            $itemsIds = array_merge($itemsIds, $itemsIdsTmp);
            $page++;

        }while(count($itemsIdsTmp)>0);

        foreach($itemsIds as $id){

            try{
                $itemData = $api->getUniverseTypesTypeId($id, CCPConfig::$datasource,'en-us');
                echo 'Item : ' . $itemData->getTypeId() . ', name : ' . $itemData->getName().'<br>';

                if($itemData->getPublished()){

                    $itemDB = $itemRep->find($id);

                    if (!$itemDB) {
                        $itemDB = new Item();
                        $itemDB->setId($id)->setName($itemData->getName())->setItemGroup($groupRep->find($itemData->getGroupId()))->setIconId($itemData->getIconId())->setVolume($itemData->getVolume());
                        $em->persist($itemDB);
                    } else {
                        $itemDB->setName($itemData->getName());
                        $itemDB->setItemGroup($groupRep->find($itemData->getGroupId()))->setIconId($itemData->getIconId())->setVolume($itemData->getVolume());
                    }
                    $em->flush();
                }
            }
            catch (ApiException $e){
                echo 'Item number : ' . $id . ' failed.';
            }



        }
        die();


    }


}