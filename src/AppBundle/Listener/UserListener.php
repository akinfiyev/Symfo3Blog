<?php

namespace AppBundle\Listener;

use AppBundle\Entity\User;
use Doctrine\ORM\Mapping\PreFlush;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserListener
{
    private $encoder;

    /**
     * UserListener constructor.
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    /** @PreFlush */
    public function preFlushHandler(User $user)
    {
        if ($user->getPlainPassword() != null)
            $user->setPassword($this->encoder->encodePassword($user, $user->getPlainPassword()));

        if (in_array('ROLE_BLOGGER', $user->getRoles()) && $user->getHasRequestBloggerRole() == true)
            $user->setHasRequestBloggerRole(false);
    }
}
