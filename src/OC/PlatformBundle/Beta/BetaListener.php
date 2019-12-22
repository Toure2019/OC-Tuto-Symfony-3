<?php
// src/OC/PlatformBundle/Beta/BetaListener.php

namespace OC\PlatformBundle\Beta;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class BetaListener
{
    // Notre processeur
    protected $betaHTML;

    // La date de fin de la version bêta :
    // - Avant cette date, on affichera un compte à rebours (J-3 par ex:)
    // - Après cette date, on n'affichera plus le « bêta »
    protected $endDate;

    public function __construct(BetaHTMLAdder $betaHTML, $endDate)
    {
        $this->betaHTML = $betaHTML;
        $this->endDate  = new \Datetime($endDate);
    }

    public function processBeta(FilterResponseEvent $event)
    {
        // On test si c'est bien la req principale et non sous-req
        if (!$event->isMasterRequest()) {
            return;
        }

        // On recupère la réponse que le gestionnaire a inséré ds l'evt
        $response = $event->getResponse();

        // Ici on modifie comme on veut la response
        $remainingDays = $this->endDate->diff(new \Datetime())->days;

        if ($remainingDays <= 0) {
            // Si la date est dépassée, on ne fait rien
            return;
        }

        // Ici on utilise notre BetaHTML
        $response = $this->betaHTML->addBeta(
            $event->getResponse(), $remainingDays
        );

        // On met à jour le reponse avec la nouvelle valeur
        $event->setResponse($response);

        // On stop la propagation de l'event en cours (ici kernel.response)
        /* La barre d'outils en bas page disparait: lié à kernel.response */
        $event->stopPropagation();
    }
}