<?php

namespace AppBundle\Service;

class EmailService
{
    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * EmailService constructor.
     * @param \Swift_Mailer $mailer
     */
    public function __construct(\Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * @param \Swift_Message $message
     */
    public function sendEmail(\Swift_Message $message)
    {
        $this->mailer->send($message);
    }
}