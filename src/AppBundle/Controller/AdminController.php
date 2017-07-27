<?php
/**
 * Created by PhpStorm.
 * User: nadan
 * Date: 27/07/2017
 * Time: 17:55
 */

namespace AppBundle\Controller;


use AppBundle\Entity\Groupe;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;


class AdminController extends Controller
{

    /**
     * @Route("/admin/group/remove/{id}", name="groupRemove")
     */
    public function adminGroupRemoveAction(Request $request, $id)
    {

        $repository = $this->getDoctrine()->getRepository(Groupe::class);

        $group = $repository->find($id);

        if($group){
            $em = $this->getDoctrine()->getManager();
            $em->remove($group);
            $em->flush();
        }

        return $this->redirect('/admin/group');
        //die('remove group');
    }


    /**
     * @Route("/admin/group", name="group")
     */
    public function adminGroupAction(Request $request)
    {
        $doctrine = $this->getDoctrine();
        $repository = $doctrine->getRepository(Groupe::class);



        $groupe = new Groupe();
        $form = $this->createFormBuilder($groupe)
            ->add('name', TextType::class)
            ->add('save', SubmitType::class, array('label' => 'Ajouter groupe'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newGroupe = $form->getData();

            $doctrine->getManager()->persist($newGroupe);
            $doctrine->getManager()->flush();
        }

        $groups = $repository->findAll();

        return $this->render('default/groupListe.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
            'groups' => $groups,
            'form' => $form->createView(),
        ]);


    }


}