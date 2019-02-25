<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Article;
use AppBundle\Form\Article\AddArticleType;
use AppBundle\Service\ArticleService;
use AppBundle\Service\UploaderService;
use AppBundle\Voter\ArticleVoter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends Controller
{
    /**
     * @var UploaderService
     */
    private $uploaderService;

    /**
     * @var ArticleService
     */
    private $articleService;

    /**
     * ArticleController constructor.
     * @param UploaderService $uploaderService
     */
    public function __construct(UploaderService $uploaderService, ArticleService $articleService)
    {
        $this->uploaderService = $uploaderService;
        $this->articleService = $articleService;
    }

    /**
     * @Route("/articles", name="articles")
     */
    public function listAction(Request $request)
    {
        $paginator = $this->get('knp_paginator');
        $articles = $paginator->paginate(
            $this->getDoctrine()->getRepository(Article::class)->findAllActiveSortedByDate(),
            $request->query->getInt('page', 1),
            5
        );


        return $this->render('@App/article/list.html.twig', [
            'articles' => $articles,
        ]);
    }

    /**
     * @Route("/articles/add", name="articles_add")
     */
    public function addAction(Request $request)
    {
        $article = new Article();

        $form = $this->createForm(AddArticleType::class, $article);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->render('@App/article/add.html.twig', [
                'form' => $form->createView()
            ]);
        }

        $article->setAuthor($this->getUser());

        if ($article->getThumbnail()) {
            $article->setThumbnail($this->uploaderService->uploadThumbnail(new UploadedFile($article->getThumbnail(), 'thumbnail')));
        }

        $this->articleService->persistTagsOfArticle($article);

        $this->getDoctrine()->getManager()->persist($article);
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('articles');
    }

    /**
     * @Route("/articles/{id}/show", name="articles_show")
     */
    public function showAction(Request $request, Article $article)
    {
        $this->denyAccessUnlessGranted(ArticleVoter::VIEW, $article);

        return $this->render('@App/article/show.html.twig', [
            'request' => $request,
            'article' => $article
        ]);
    }
}
