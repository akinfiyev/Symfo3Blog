<?php


namespace AppBundle\Listener;


use AppBundle\Entity\Article;
use Doctrine\ORM\Mapping\PrePersist;

class ArticleListener
{
    /** @PrePersist() */
    public function prePersistHandler(Article $article)
    {
        if ($article->getCreatedAt() == null) {
            $article->setCreatedAt(new \DateTime());
        }

        if ($article->getIsDeleted() == null) {
            $article->setIsDeleted(false);
        }

        if ($article->getIsApproved() == null) {
            $article->setIsApproved(false);
        }
    }

}