<?php


namespace AppBundle\Controller;

use AppBundle\Entity\Article;
use AppBundle\Entity\UserLike;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserLikeController extends Controller
{
    /**
     * @Route("/like/{id}", name="like_article")
     */
    public function likeArticleAction(Request $request, Article $article)
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('user_registration');
        }

        $em = $this->getDoctrine()->getManager();
        $like = $em->getRepository(UserLike::class)
            ->findOneBy([
                'user' => $this->getUser(),
                'article' => $article->getId()
            ]);

        if ($like) {
            $em->remove($like);
            $em->flush();

            return new JsonResponse(count($article->getArticleLikes()));
        }

        $like = new UserLike();
        $like->setUser($this->getUser())
            ->setArticle($article);
        $em->persist($like);
        $em->flush();

        return new JsonResponse(count($article->getArticleLikes()));


    }
}