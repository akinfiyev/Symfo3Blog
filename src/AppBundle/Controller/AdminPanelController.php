<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Article;
use AppBundle\Entity\Comment;
use AppBundle\Entity\User;
use AppBundle\Form\Article\AddArticleType;
use AppBundle\Form\User\EditProfileType;
use AppBundle\Service\ArticleService;
use AppBundle\Service\UploaderService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AdminPanelController extends Controller
{
    /**
     * @var ArticleService
     */
    private $articleService;

    /**
     * AdminPanelController constructor.
     * @param ArticleService $articleService
     */
    public function __construct(ArticleService $articleService)
    {
        $this->articleService = $articleService;
    }


    /**
     * @Route("/admin/", name="admin_panel")
     */
    public function indexAction()
    {
        return $this->render('@App/admin_panel/index.html.twig');
    }

    /**
     * @Route("/admin/users/", name="admin_panel_users")
     */
    public function usersListAction(Request $request, PaginatorInterface $paginator)
    {
        $query = $this->getDoctrine()
            ->getRepository(User::class)
            ->createQueryBuilder('user')
            ->orderBy('user.id', 'ASC')
            ->getQuery();
        $users = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            5
        );
        return $this->render('@App/admin_panel/user/list.html.twig', [
            'users' => $users
        ]);
    }

    /**
     * @Route("/admin/users/blogger_requests", name="admin_panel_users_blogger_requests")
     */
    public function usersListBloggerRequestsAction(Request $request, PaginatorInterface $paginator)
    {
        $query = $this->getDoctrine()
            ->getRepository(User::class)
            ->createQueryBuilder('user')
            ->where('user.hasRequestBloggerRole = true')
            ->orderBy('user.id', 'ASC')
            ->getQuery();
        $users = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            5
        );
        return $this->render('@App/admin_panel/user/list.html.twig', [
            'users' => $users
        ]);
    }

    /**
     * @Route("/admin/users/{id}/edit", methods={"GET", "POST"}, name="admin_panel_users_edit")
     */
    public function usersEditAction(Request $request, User $user, UploaderService $uploaderService)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->find($user);
        $avatar = $user->getAvatar();

        $form = $this->createForm(EditProfileType::class, $user);
        $form->handleRequest($request);
        if (!$form->isSubmitted() && !$form->isValid()) {
            return $this->render('@App/admin_panel/user/edit_profile.html.twig', [
                'user' => $user,
                'form' => $form->createView()
            ]);
        }

        if ($user->getAvatar() !== null)
            $avatar = $uploaderService->uploadAvatar(new UploadedFile($user->getAvatar(), 'avatar'));

        $user->setAvatar($avatar);
        $em->flush();

        return $this->render('@App/admin_panel/user/edit_profile.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'message' => 'success'
        ]);
    }

    /**
     * @Route("/admin/users/{id}/make_blogger", name="admin_panel_users_make_blogger")
     */
    public function usersMakeBloggerAction(Request $request, User $user)
    {
        $roles[] = in_array('ROLE_BLOGGER', $user->getRoles()) ? 'ROLE_USER' : 'ROLE_BLOGGER';
        $user->setRoles($roles);

        $this->getDoctrine()->getManager()->flush();

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/admin/users/{id}/ban/", name="admin_panel_users_ban")
     */
    public function usersBanAction(Request $request, User $user)
    {
        $em = $this->getDoctrine()->getManager();

        $user->setRoles(in_array('ROLE_BANNED', $user->getRoles()) ? ['ROLE_USER'] : ['ROLE_BANNED']);

        $em->flush();

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/admin/articles/", name="admin_panel_articles")
     */
    public function articlesListAction(Request $request, PaginatorInterface $paginator)
    {
        $query = $this->getDoctrine()
            ->getRepository(Article::class)
            ->createQueryBuilder('article')
            ->where('article.isDeleted = false')
            ->orderBy('article.id', 'DESC')
            ->getQuery();
        $articles = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            5
        );
        return $this->render('@App/admin_panel/article/list.html.twig', [
            'articles' => $articles
        ]);
    }

    /**
     * @Route("/admin/articles/unapproved", name="admin_panel_articles_unapproved")
     */
    public function articlesListUnapprovedAction(Request $request, PaginatorInterface $paginator)
    {
        $query = $this->getDoctrine()
            ->getRepository(Article::class)
            ->createQueryBuilder('article')
            ->where('article.isDeleted = false')
            ->andWhere('article.isApproved = false')
            ->orderBy('article.id', 'DESC')
            ->getQuery();
        $articles = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            5
        );
        return $this->render('@App/admin_panel/article/list.html.twig', [
            'articles' => $articles
        ]);
    }

    /**
     * @Route("/admin/articles/add", methods={"GET","POST"}, name="admin_panel_articles_add")
     * @throws \Exception
     */
    public function articleAddAction(Request $request, UploaderService $uploaderService)
    {
        $article = new Article();

        $form = $this->createForm(AddArticleType::class, $article);

        $form->handleRequest($request);
        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->render('@App/admin_panel/article/add.html.twig', [
                'form' => $form->createView()
            ]);
        }

        $em = $this->getDoctrine()->getManager();
        $article->setAuthor($this->getUser())
            ->setCreatedAt(new \DateTime())
            ->setIsDeleted(false)
            ->setIsApproved(false);
        if (!empty($article->getThumbnail())) {
            $thumbnail = $uploaderService->uploadThumbnail(new UploadedFile($article->getThumbnail(), 'thumbnail'));
            $article->setThumbnail($thumbnail);
        }
        if (!empty($article->getPlainTags())) {
            $tags = $this->articleService->parseTags($article->getPlainTags(), $article);
            foreach ($tags as $tag) {
                $em->persist($tag);
            }

        }
        $em->persist($article);
        $em->flush();

        return $this->redirectToRoute('admin_panel_articles');
    }


    /**
     * @Route("/admin/articles/{id}/edit", name="admin_panel_articles_edit")
     */
    public function articleEditAction(Request $request, Article $article)
    {
        $savedThumbnail = $this->articleService->articlePreEdit($article);

        $form = $this->createForm(AddArticleType::class, $article);
        $form->handleRequest($request);
        if (!$form->isSubmitted() && !$form->isValid()) {
            return $this->render('@App/admin_panel/article/edit.html.twig', [
                'article' => $article,
                'form' => $form->createView()
            ]);
        }

        $this->articleService->articleThumbnailEdit($article, $savedThumbnail);
        $this->articleService->persistTagsOfArticle($article);

        $this->getDoctrine()->getManager()->persist($article);
        $this->getDoctrine()->getManager()->flush();

        return $this->render('@App/admin_panel/article/edit.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
            'message' => 'success'
        ]);
    }

    /**
     * @Route("/admin/articles/{id}/approve", name="admin_panel_articles_approve")
     */
    public function articleApproveAction(Request $request, Article $article)
    {
        $article->setIsApproved(true);
        $this->getDoctrine()->getManager()->flush();

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/admin/articles/{id}/delete", name="admin_panel_articles_delete")
     */
    public function articleDeleteAction(Request $request, Article $article)
    {
        $article->setIsDeleted(true);
        $this->getDoctrine()->getManager()->flush();

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/admin/comments", name="admin_panel_comments")
     */
    public function commentsListAction(Request $request, PaginatorInterface $paginator)
    {
        $query = $this->getDoctrine()
            ->getRepository(Comment::class)
            ->createQueryBuilder('comment')
            ->where('comment.isDeleted = false')
            ->orderBy('comment.id', 'DESC')
            ->getQuery();
        $comments = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            5
        );
        return $this->render('@App/admin_panel/comment/list.html.twig', [
            'comments' => $comments
        ]);
    }

    /**
     * @Route("/admin/comments/{id}/delete", name="admin_panel_comments_delete")
     */
    public function commentsDeleteAction(Request $request, Comment $comment)
    {
        $comment->setIsDeleted(true);
        $this->getDoctrine()->getManager()->flush();

        return $this->redirect($request->headers->get('referer'));
    }
}