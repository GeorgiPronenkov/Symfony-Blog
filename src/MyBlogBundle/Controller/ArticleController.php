<?php

namespace MyBlogBundle\Controller;

use MyBlogBundle\Entity\Article;
use MyBlogBundle\Entity\Comment;
use MyBlogBundle\Entity\User;
use MyBlogBundle\Form\ArticleType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends Controller
{
    /**
     * @Route("/article/create", name="article_create")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param Request $request
     * @return Response
     */
    public function createAction(Request $request)
    {
//        създ.инстанция от Article модела:
        $article = new Article();
//        създаваме форма;
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {

            /**
             * @var UploadedFile $file
             */
            $file = $form->getData()->getImage();
            $fileName = md5(uniqid()) . '.' . $file->guessExtension();

            try {
                $file->move($this->getParameter('article_directory'), $fileName);
            }catch (FileException $ex) {

            }

//            set image
            $article->setImage($fileName);

//            set authorId
            $currentUser = $this->getUser();
            $article->setAuthor($currentUser);
            $article->setViewCount(0);
            $em = $this->getDoctrine()->getManager();
            $em->persist($article);
            $em->flush(); //запиши записа

            return $this->redirectToRoute("blog_index");
        }

        return $this->render('article/create.html.twig',
            [
              'form' => $form->createView()
            ]);
    }


//method for getting single article
    /**
     * @Route("/article/{id}", name="article_view")
     * @param $id
     * @return Response
     */
    public function viewArticle($id) {

//        find single article:
        $article = $this->getDoctrine()
                        ->getRepository(Article::class)
                        ->find($id);

        $comments = $this->getDoctrine()
                         ->getRepository(Comment::class)
                         ->findAllComments($article);

//       set view count of article
        $article->setViewCount($article->getViewCount() + 1);
        $em = $this->getDoctrine()->getManager();
        $em->persist($article);
        $em->flush();

        return $this->render("article/article.html.twig",
            [
               'article' => $article,
               'comments' => $comments
            ]);
    }

    /**
     * @Route("/article/edit/{id}", name="article_edit")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function editAction(Request $request, $id)
    {
//        намира article-a от базата:
        $article = $this->getDoctrine()
                        ->getRepository(Article::class)
                        ->find($id);

        if ($article === null) {
            return  $this->redirectToRoute("blog_index");
        }

        //Validating the Edit Article Function
        /**
         * @var User $currentUser
         */
        $currentUser = $this->getUser(); //???  ако не е автор и админ:
        if (!$currentUser->isAuthor($article) && !$currentUser->isAdmin()) {
            return $this->redirectToRoute("blog_index");
        }

//        създаваме форма:
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /**
             * @var UploadedFile $file
             */
            $file = $form->getData()->getImage();

            $fileName = md5(uniqid()) . '.' . $file->guessExtension();

            try {
                $file->move($this->getParameter('article_directory'), $fileName);
            }catch (FileException $ex) {

            }

//            set image
            $article->setImage($fileName);

//            set authorId
            $currentUser = $this->getUser();
            $article->setAuthor($currentUser);
            $em = $this->getDoctrine()->getManager();
            $em->merge($article);
            $em->flush(); //запиши записа

            return $this->redirectToRoute("blog_index");
        }

        return $this->render('article/edit.html.twig',
            [
              'form' => $form->createView(),
              'article' => $article
            ]);
    }

    /**
     * @Route("/article/delete/{id}", name="article_delete")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function deleteAction(Request $request, $id)
    {
//        намира article-a от базата:
        $article = $this->getDoctrine()
                        ->getRepository(Article::class)
                        ->find($id);

        if ($article === null) {
            return  $this->redirectToRoute("blog_index");
        }

        /**
         * @var User $currentUser
         */
        $currentUser = $this->getUser(); //???
        if (!$currentUser->isAuthor($article) && !$currentUser->isAdmin()) {
            return $this->redirectToRoute("blog_index");
        }

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //set authorId
            $currentUser = $this->getUser();
            $article->setAuthor($currentUser);
            $em = $this->getDoctrine()->getManager();
            $em->remove($article);
            $em->flush(); //запиши записа

            return $this->redirectToRoute("blog_index");
        }

        return $this->render('article/delete.html.twig',
            [
              'form' => $form->createView(),
              'article' => $article
            ]);
    }

    //create article success msg show

    /**
     * @Route("/myArticles", name="myArticles")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function myArticles() {

        $articles = $this->getDoctrine()
                         ->getRepository(Article::class)
                         ->findBy(
                             [
                               'author' => $this->getUser()
                             ]);

        return $this->render("article/myArticles.html.twig",
            ['articles' => $articles]);
    }

    //method for likes counter

    /**
     * @Route("/article/like/{id}", name="article_likes")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param $id
     * @return RedirectResponse
     */
    public function likes($id)
    {
        return $this->redirectToRoute("blog_index");
    }

}
