<?php


namespace AppBundle\Service;


class TagService
{
    public function createTagNamesArray(array $tags)
    {
        $tagNames = [];
        foreach ($tags as $tag) {
            if (!in_array($tag->getName(), $tagNames)) {
                $tagNames[] = $tag->getName();
            }
        }

        return $tagNames;
    }
}