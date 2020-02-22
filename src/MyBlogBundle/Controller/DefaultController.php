<?php

namespace MyBlogBundle\Controller;

use MyBlogBundle\Entity\Article;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class DefaultController extends Controller
{
    const LIMIT = 4;
    /**
     * @Route("/", name="blog_index")
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request)
    {
//        get all Articles from base
        $articles = $this->getDoctrine()
                         ->getRepository(Article::class)
                         ->findBy([],
                           [
                            'viewCount' => 'desc',
                            'dateAdded' => 'desc'
                           ]);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $articles, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            self::LIMIT /*limit per page*/
        );

        return $this->render('default/index.html.twig',
            [
               'pagination' => $pagination
            ]);
    }
}
