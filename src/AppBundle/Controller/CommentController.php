<?php


namespace AppBundle\Controller;


use AppBundle\Entity\Article;
use AppBundle\Entity\Comment;
use AppBundle\Form\Comment\AddCommentType;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CommentController extends Controller
{
    /**
     * @var PaginatorInterface
     */
    private $paginator;

    /**
     * CommentController constructor.
     * @param PaginatorInterface $paginator
     */
    public function __construct(PaginatorInterface $paginator)
    {
        $this->paginator = $paginator;
    }


    public function listCommentsAction(Request $request, Article $article)
    {
        $comment = new Comment();
        $form = $this->createForm(AddCommentType::class, $comment, [
            'action' => $this->generateUrl('comments_add', ['id' => $article->getId()])
        ]);

        $comments = $this->paginator->paginate(
            $this->getDoctrine()->getRepository(Comment::class)->findAllByArticle($article),
            $request->query->getInt('page', 1),
            5
        );

        return $this->render('@App/comment/list.html.twig', [
            'comments' => $comments,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/articles/{id}/comment/add", name="comments_add")
     */
    public function addCommentAction(Request $request, Article $article)
    {
        $comment = new Comment();

        $form = $this->createForm(AddCommentType::class, $comment);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setArticle($article)
                ->setAuthor($this->getUser());

            $this->getDoctrine()->getManager()->persist($comment);
            $this->getDoctrine()->getManager()->flush();
        }

        return $this->redirect($request
            ->headers
            ->get('referer'));
    }
}