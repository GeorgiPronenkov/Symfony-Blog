<?php

namespace MyBlogBundle\Controller;

use MyBlogBundle\Entity\Article;
use MyBlogBundle\Entity\Comment;
use MyBlogBundle\Entity\User;
use MyBlogBundle\Form\CommentType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CommentController extends Controller
{
    /**
     * @Route("/article/{id}/comment", name="add_comment")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param Request $request
     * @param Article $article
     * @return RedirectResponse
     */
    public function addComment(Request $request, Article $article) //Request- за да попълни формата
    {
        $user = $this->getUser();//взема тек.логнат потребител

        /**
         * @var User $author
         */
        $author = $this->getDoctrine()
                       ->getRepository(User::class)
                       ->find($user->getId());

//        create instance of entity comment:
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment); //create empty form
        $form->handleRequest($request);

        $comment->setAuthor($author);
        $comment->setArticle($article);

        $author->addComment($comment); //!!!
        $article->addComment($comment);

//       изпълнение на всичките транзакции и записване в базата:
        $em = $this->getDoctrine()->getManager();
        $em->persist($comment);
        $em->flush();

//        redirect route
        return $this->redirectToRoute('article_view',
            ['id' => $article->getId()]); //!!!!
    }
}
