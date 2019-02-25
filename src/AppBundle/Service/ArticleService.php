<?php


namespace AppBundle\Service;


use AppBundle\Entity\Article;
use AppBundle\Entity\Tag;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ArticleService
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var UploaderService
     */
    private $uploaderService;

    /**
     * ArticleService constructor.
     * @param ObjectManager $om
     * @param UploaderService $uploaderService
     */
    public function __construct(ObjectManager $om, UploaderService $uploaderService)
    {
        $this->om = $om;
        $this->uploaderService = $uploaderService;
    }


    /**
     * @param string $plainTags
     * @param Article $article
     * @return array
     */
    public function parseTags(string $plainTags, Article $article)
    {
        $plainTags = trim($plainTags);
        $plainTags = explode(", ", $plainTags);
        $plainTags = array_unique($plainTags);

        $tags = [];
        foreach ($plainTags as $plainTag) {
            if (trim($plainTag) == '') {
                continue;
            }
            $tag = new Tag();
            $tag->setName($plainTag)
                ->setArticle($article);
            $tags[] = $tag;
        }

        return $tags;
    }

    /**
     * Returned saved at article thumbnail
     *
     * @param Article $article
     * @return string|null
     *
     */
    public function articlePreEdit(Article $article)
    {
        if (count($article->getTags())) {
            $article->setPlainTags(implode(", ", $article->getTags()->toArray()));
        }

        return $article->getThumbnail() != null && $article->getThumbnail() != '' ? $article->getThumbnail() : '';
    }

    /**
     * @param Article $article
     * @param $savedThumbnail
     * @throws \Exception
     */
    public function articleThumbnailEdit(Article $article, $savedThumbnail)
    {
        if (empty($article->getThumbnail()) && !empty($savedThumbnail)) {
            $article->setThumbnail($savedThumbnail);
            return;
        }

        if (!empty($article->getThumbnail())) {
            $thumbnail = $this->uploaderService->uploadThumbnail(new UploadedFile($article->getThumbnail(), 'thumbnail'));
            $article->setThumbnail($thumbnail);
        }
    }

    /**
     * Check if tag exist to avoid duplication
     *
     * @param Tag $tag
     * @return bool
     */
    public function checkIfTagExist(Tag $tag)
    {
        $result = $this->om->getRepository(Tag::class)
            ->findOneBy([
                'name' => $tag->getName(),
                'article' => $tag->getArticle()->getId(),
            ]);

        return $result != null ? true : false;
    }

    public function persistTagsOfArticle(Article $article)
    {
        if (empty($article->getPlainTags()))
            return;

        $tags = $this->parseTags($article->getPlainTags(), $article);

        foreach ($tags as $tag) {
            $this->om->persist($tag);
        }
    }
}