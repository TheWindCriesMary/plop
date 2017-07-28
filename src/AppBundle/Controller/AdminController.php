<?php
/**
 * Created by PhpStorm.
 * User: nadan
 * Date: 27/07/2017
 * Time: 17:55
 */

namespace AppBundle\Controller;


use AppBundle\CCP\CCPConfig;
use AppBundle\Entity\Groupe;
use AppBundle\Entity\User;
use nullx27\ESI\Api\CorporationApi;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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

    /**
     * @Route("/admin/member/{id}", name="member")
     */
    public function adminMemberAction(Request $request, $id)
    {




        $doctrine = $this->getDoctrine();
        $rep = $doctrine->getRepository(User::class);

        $user = $rep->find($id);

        $rep = $this->getDoctrine()->getRepository(Groupe::class);

        $groups = $rep->findAll();


        //TODO set the selected group (select="selected")

        $userForm = new User();
        $groupForm = $this->createFormBuilder($userForm)
            ->add('Groupe', EntityType::class, array(
                'class' => 'AppBundle:Groupe',
                'choice_label' => 'name',
            ))
            ->add('save', SubmitType::class, array('label' => 'Changer groupe'))
            ->getForm();


        $groupForm->handleRequest($request);

        if ($groupForm->isSubmitted() && $groupForm->isValid()) {
            $user->setGroupe( $groupForm->getData()->getGroupe());

            $doctrine->getManager()->persist($user);
            $doctrine->getManager()->flush();
        }


        return $this->render('admin/member.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
            'member' => $user,
            'group_form' => $groupForm->createView(),
        ]);


    }

    /**
     * @Route("/admin/members", name="members")
     */
    public function adminMemberListAction(Request $request)
    {
        $corpAPI = new CorporationApi();

        $rep = $this->getDoctrine()->getRepository(User::class);



        $users = null;
        $users = $rep->findAll();

        foreach ($users as $user){

            $corp = $corpAPI->getCorporationsCorporationId($user->getCorpId(), CCPConfig::$datasource);

            $user->corp = $corp;
        }


        return $this->render('admin/members.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
            'members' => $users,
        ]);

        //die('members');

    }



}