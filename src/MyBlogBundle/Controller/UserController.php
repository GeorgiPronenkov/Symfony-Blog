<?php

namespace MyBlogBundle\Controller;

use MyBlogBundle\Entity\Message;
use MyBlogBundle\Entity\Role;
use MyBlogBundle\Entity\User;
use MyBlogBundle\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends Controller
{
    /**
     * @Route("/register", name="user_register")
     * @param  Request $request
     * @return Response
     */
    public function registerAction(Request $request)
    {
//        instance of user from entity User:
        $user = new User();

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request); // прихваща request-a

        if ($form->isSubmitted()) {

//            проверка за съществ.email;
            $emailForm = $form->getData()->getEmail();

            $userForm = $this ->getDoctrine() //конкректния user който взимаме от формата
                              ->getRepository(User::class)
                              ->findOneBy(['email' => $emailForm]);

            if  ($userForm !== null) { // потр. вече съществува (има такъв регистриран user)
                $this->addFlash(
                    'info',
                    "Username with email " . $emailForm . " already taken!");

                return $this->render('user/register.html.twig',
                    ['form' => $form->createView()]);
            }

//           crypt password
            $password = $this->get('security.password_encoder')
                             ->encodePassword($user, $user->getPassword());

//            set user's role:
            $role = $this->getDoctrine()
                         ->getRepository(Role::class)
                         ->findOneBy(['name' => 'ROLE_USER']);

            $user->addRole($role);

//            set user's password with crypted password:
            $user->setPassword($password);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user); //създава заявката
            $em->flush();       //изпълнява заявката към базата

            return $this->redirectToRoute('security_login');
        }

        return $this->render('user/register.html.twig',
            ['form' => $form->createView()]);
    }

    /**
     * @Route("user/profile", name="user_profile")
     */
    public function profile() {

//        взема тек.логнат потребител:
        $userId = $this->getUser()->getId();
//        get user from db:
        $user = $this->getDoctrine()
                     ->getRepository(User::class)
                     ->find($userId);


        $unreadMessages = $this->getDoctrine()
                               ->getRepository(Message::class)
                               ->findBy(
                                 [
                                  'recipient' => $user,
                                  'isReader' => false
                                 ]);

        $countMsg = count($unreadMessages);

        return $this->render('user/profile.html.twig',
            [
             'user' => $user,
             'countMsg' => $countMsg
            ]);
    }
}
