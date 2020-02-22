<?php

namespace MyBlogBundle\Controller;

use MyBlogBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * Class AdminController
 * @Route("admin")
 * @package MyBlogBundle\Controller
 */
class AdminController extends Controller
{
    /**
     * @Route("/", name="all_users")
     * @return Response
     */
    public function indexAction()
    {
//        get all users
        $allUsers = $this->getDoctrine()
                         ->getRepository(User::class)
                         ->findAll();

//        show users in admin panel
        return $this->render('admin/index.html.twig',
            ['allUsers' => $allUsers]);
    }

    /**
     * @Route("/user_profile/{id}", name="admin_user_profile")
     * @param $id
     * @return Response
     */
    public function userProfile($id)
    {
        $user = $this->getDoctrine()
                     ->getRepository(User::class)
                     ->find($id);

        return $this->render('admin/user_profile.html.twig',
            ['user' => $user]);
    }
}
