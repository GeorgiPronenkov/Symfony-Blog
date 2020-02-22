<?php

namespace MyBlogBundle\Controller;

use MyBlogBundle\Entity\Message;
use MyBlogBundle\Entity\User;
use MyBlogBundle\Form\MessageType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MessageController extends Controller
{
    /**
     * @Route("/user/{id}/message/{articleId}", name="user_message")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param Request $request
     * @param $articleId
     * @param $id
     *
     *
     * @return Response
     */
    public function addMessageAction(Request $request, $articleId, $id)
    {
//       get last index of
//        $articleIdFromRequest = substr($_SERVER['HTTP_REFERER'],
//                          strrpos($_SERVER['HTTP_REFERER'], '/') + 1);

       $currentUser = $this->getUser();

//       $message = $this->getDoctrine()->getRepository(Message::class)->findOneBy([''])

       $recipient = $this->getDoctrine()
                         ->getRepository(User::class)
                         ->find($id);

       $message = new Message();
       $form = $this->createForm(MessageType::class, $message);
       $form->handleRequest($request);

       if ($form->isSubmitted()) {
           $message->setSender($currentUser)
                   ->setRecipient($recipient)
                   ->setIsReader(false);

           $em = $this->getDoctrine()->getManager();
           $em->persist($message);
           $em->flush();

           $this->addFlash("message", "Message has been sent!!!");

           return $this->redirectToRoute("article_view",
               ['id' => $articleId]);
       }

       return $this->render('user/send_message.html.twig',
           ['form' => $form->createView()]);
    }

    /**
     * @Route("user/mailbox", name="user_mailbox")
     * @param Request $request
     * @return Response
     */
    public function mailBox(Request $request)
    {
        $currentUserId = $this->getUser()->getId();

        $user = $this->getDoctrine()
                     ->getRepository(User::class)
                     ->find($currentUserId);

        $messages = $this->getDoctrine()
                         ->getRepository(Message::class)
                         ->findBy(['recipient' => $user]);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $messages, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            5 /*limit per page*/
        );

        return $this->render("user/mailbox.html.twig",
            ['pagination' => $pagination]);
    }

    /**
     * @Route("/mailbox/message/{id}", name="user_current_message")
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function currentMessageAction(Request $request, $id)
    {
//        single message find
        $message = $this->getDoctrine()
                        ->getRepository(Message::class)
                        ->find($id);

//        set readed message:
        $message->setIsReader(true);
        $em = $this->getDoctrine()->getManager();
        $em->persist($message);
        $em->flush();

//        crete form for "add message here:
        $sendMessage = new Message(); //instance
        $form = $this->createForm(MessageType::class, $sendMessage);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $sendMessage
                ->setSender($this->getUser())
                ->setRecipient($message->getSender())
                ->setIsReader(false);

            $em->persist($sendMessage);
            $em->flush();

            $this->addFlash("message", "Message has been sent!!!");

            return $this->redirectToRoute("user_current_message",
                ['id' => $id]);
        }
        
        return $this->render("user/message.html.twig",
            ['message' => $message,
             'form' => $form->createView()
            ]);
    }
}

