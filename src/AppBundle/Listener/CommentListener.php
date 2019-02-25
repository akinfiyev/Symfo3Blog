<?php

namespace AppBundle\Listener;

use AppBundle\Entity\Comment;
use Doctrine\ORM\Mapping\PrePersist;

class CommentListener
{
    /** @PrePersist() */
    public function prePersistHandler(Comment $comment)
    {
        if ($comment->getCreatedAt() == null) {
            $comment->setCreatedAt(new \DateTime());
        }

        if ($comment->getIsDeleted() == null) {
            $comment->setIsDeleted(false);
        }
    }
}