<?php
// src/OC/PlatformBundle/Event/MessagePostEvent.php

namespace OC\PlatformBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Security\Core\User\UserInterface;

class MessagePostEvent extends Event
{
    protected $message;
    protected $user;

    public function __construct($message, UserInterface $user)
    {
        $this->message = $message;
        $this->user = $user;
    }

    // Le listener doit avoir accès au message
    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage($message)  // modifier le message
    {
        return $this->message = $message;
    }

    public function getUser()   //Accès à l'utilisateur
    {
        return $this->user;
    }
    // Pas de setUser, les listeners ne peuvent pas modifier l'auteur du msg
}