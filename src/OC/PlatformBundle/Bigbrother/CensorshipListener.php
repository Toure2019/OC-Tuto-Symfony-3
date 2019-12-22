<?php
// src/OC/PlatformBundle/Bigbrother/CensorshipListener.php

/* Concrètement, c'est l'objet souscripteur lui-même qui va dire au gestionnaire d'évènements les différents évènements qu'il veut écouter. Pour cela, un souscripteur doit implémenter l'interfaceEventSubscriberInterface, qui ne contient qu'une seule méthode :getSubscribedEvents(). Vous l'avez compris, cette méthode doit retourner les évènements que le souscripteur veut écouter.

Voici par exemple comment on pourrait transformer notreMessageListener en un souscripteur : */

namespace OC\PlatformBundle\Bigbrother;

use OC\PlatformBundle\Event\PlatformEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MessageListener implements EventSubscriberInterface
{
  // La méthode de l'interface que l'on doit implémenter, à définir en static
  static public function getSubscribedEvents()
  {
    // On retourne un tableau « nom de l'évènement » => « méthode à exécuter » + 'information de la priorité (2, 1)
    return array(
      PlatformEvents::POST_MESSAGE    => ['processMessage' => 2],
      PlatformEvents::AUTRE_EVENEMENT => ['autreMethode' => 1],
      // ...
    );
  }

  public function processMessage(MessagePostEvent $event)
  {
    // ...
  }

  public function autreMethode()
  {
    // ...
  }
}

// PLUS D'INFOS SUR LES SOUSCRIPTEURS D'EVENEMENT

/* Bien sûr, il faut ensuite déclarer ce souscripteur au gestionnaire d'évènements. Pour cela, ce n'est plus le tagkernel.event_listener qu'il faut utiliser, mais :kernel.event_subscriber. Avec ce tag, le gestionnaire d'évènement récupère tous les souscripteurs d'évènements et les enregistre.

Pas besoin d'ajouter les attributs event et method sur le tag, car c'est la méthodegetSubscribedEvents qui retourne ces informations : */

/* # src/OC/PlatformBundle/Resources/config/services.yml

services:
    oc_platform.bigbrother.message_listener:
        class: OC\PlatformBundle\Bigbrother\MessageListener
        arguments:
            - "@oc_platform.bigbrother.message_notificator"
            - ["alexandre", "marine", "pierre"]
        tags:
            - { name: kernel.event_subscriber } */