<?php
namespace OC\PlatformBundle\Controller;

use OC\PlatformBundle\Entity\Image;
use OC\PlatformBundle\Entity\Advert;
use OC\PlatformBundle\Entity\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AdvertController extends Controller
{
    public function menuAction($limit)
    {
        // On fixe en dur une liste ici, bien entendu par la suite
        // on la récupérera depuis la BDD !
        $listAdverts = array(
            array('id' => 2, 'title' => 'Recherche développeur Symfony'),
            array('id' => 5, 'title' => 'Mission de webmaster'),
            array('id' => 9, 'title' => 'Offre de stage webdesigner')
        );
  
      return $this->render('@OCPlatform/Advert/menu.html.twig', array(
        // Tout l'intérêt est ici : le contrôleur passe
        // les variables nécessaires au template !
        'listAdverts' => $listAdverts
      ));
    }

    public function indexAction($page)
    {
        if ($page < 1) {
            throw $this->createNotFoundException('page "'.$page.'" inexistante.');
        }

        // Notre liste d'annonce en dur
        $listAdverts = array(
            array(
            'title'   => 'Recherche développpeur Symfony',
            'id'      => 1,
            'author'  => 'Alexandre',
            'content' => 'Nous recherchons un développeur Symfony débutant sur Lyon. Blabla…',
            'date'    => new \Datetime()),
            array(
            'title'   => 'Mission de webmaster',
            'id'      => 2,
            'author'  => 'Hugo',
            'content' => 'Nous recherchons un webmaster capable de maintenir notre site internet. Blabla…',
            'date'    => new \Datetime()),
            array(
            'title'   => 'Offre de stage webdesigner',
            'id'      => 3,
            'author'  => 'Mathieu',
            'content' => 'Nous proposons un poste pour webdesigner. Blabla…',
            'date'    => new \Datetime())
        );
        return $this->render('@OCPlatform/Advert/index.html.twig', [
            'listAdverts' => $listAdverts
        ]);
    }


    public function viewAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('OCPlatformBundle:Advert');
        
        // On récupère l'entité correspondante à l'id
        $advert = $repo->find($id);
        if (null == $advert) {
            throw new NotFoundException("L'annonce ".$id." n'existe pas");
        } 

        // On récupère la liste des candidatures de cette annonce
        $listApplications = $em
            ->getRepository('OCPlatformBundle:Application')
            ->findBy(array('advert' => $advert));

        return $this->render('@OCPlatform/Advert/view.html.twig', [
            'advert' => $advert,
            'listApplications' => $listApplications
        ]);
    }


    public function addAction(Request $request)
    {
        // Création de l'entité
        $advert = new Advert();
        $advert->setTitle('Recherche de developpeur Symfony 3');
        $advert->setAuthor('Thomas');
        $advert->setContent('Nous recherchons un développeur Symfony 3 débutant sur Paris...');

        // Création de l'entité Image
        $image = new Image();
        $image->setUrl('http://sdz-upload.s3.amazonaws.com/prod/upload/job-de-reve.jpg');
        $image->setAlt('Job de rêve');
        // On lie l'image à l'annonce
        $advert->setImage($image);

        // Création d'une première candidature
        $application1 = new Application();
        $application1->setAuthor('Marine');
        $application1->setContent("J'ai toutes les qualités requises.");

        // Création d'une deuxième candidature par exemple
        $application2 = new Application();
        $application2->setAuthor('Pierre');
        $application2->setContent("Je suis très motivé.");

        // On lie les candidatures à l'annonce
        $application1->setAdvert($advert);
        $application2->setAdvert($advert);

        // On récupère l'EntityManager
        $em = $this->getDoctrine()->getManager();

        // Etape 1: On persist l'entité
        $em->persist($advert);
        $em->persist($application1);
        $em->persist($application2);

        // Étape 1 bis : si on n'avait pas défini le cascade={"persist"},
        // on devrait persister à la main l'entité $image
        // $em->persist($image);

        // Etape 2: On "flush" tout ce qui a été persisté
        $em->flush();

        // // On récupère le service
        // $antispam = $this->container->get('oc_platform.antispam');
        // // $text contient le txt d1 msg qlqconque
        // $text = 'Un message quelconque';
        // if ($antispam->isSpam($text)) {
        //     throw new \Exception('Votre message a été détecté com spam !');
        // }

        if ($request->isMethod('POST')) {
            $this->addFlash('notice', 'Annonce bien enregistrée.');
            return $this->redirectToRoute('oc_avdvert_view', [
                'id' => $advert->getId()
            ]);
        }
        return $this->render('@OCPlatform/Advert/add.html.twig', [
            'advert' => $advert
        ]);
    }


    public function editAction(Request $request, $id)
    {
        if ($request->isMethod('POST')) {
            $this->addFlash('notice', 'Annonce bien modifiée.');
            return $this->redirectToRoute('oc_advert_view', ['id' => 5]);
        }

        /* Modification d'une image */ 
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('OCPlatformBundle:Advert');
        $advert = $repo->find($id);
        if (null === $advert) {
            throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
        }
        $advert->getImage()->setUrl("https://place-hold.it/300");

         // La méthode findAll retourne toutes les catégories de la base de données
        $listCategories = $em->getRepository('OCPlatformBundle:Category')->findAll();

        // On boucle sur les catégories pour les lier à l'annonce
        foreach ($listCategories as $category) {
            $advert->addCategory($category);
        }
  
      // Pour persister le changement dans la relation, il faut persister l'entité propriétaire
      // Ici, Advert est le propriétaire, donc inutile de la persister car on l'a récupérée depuis Doctrine

        $em->flush();

        return $this->render('@OCPlatform/Advert/edit.html.twig', [
            'advert' => $advert
        ]);
    }


    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        // On récupère l'annonce $id
        $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

        if (null === $advert) {
        throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
        }

        // On boucle sur les catégories de l'annonce pour les supprimer
        foreach ($advert->getCategories() as $category) {
            $advert->removeCategory($category);
        }

        // Pour persister le changement dans la relation, il faut persister l'entité propriétaire
        // Ici, Advert est le propriétaire, donc inutile de la persister car on l'a récupérée depuis Doctrine

        // On déclenche la modification
        $em->flush();

        return $this->render('@OCPlatform/Advert/delete.html.twig');
    }


    public function viewSlugAction($slug, $year, $_format)
    {
        return new Response(
            "On pourrait afficher l'annonce correspondant au
            slug '".$slug."', créée en ".$year." et au format ".$_format."."
          );
    }

}


// ++++ TESTS : ENTREE EN MATIERE
/*
// use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route as RouteGen;
use Symfony\Component\Routing\RouteCollection;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
*/

// class AdvertController extends AbstractController // Pour générer URL
// class AdvertController extends Controller
// {
    // Route de test : entrée en matière
    /* public function index1Action()
    {
        return $this->render('@OCPlatform/Advert/index.html.twig', [
            'name' => 'Winzou'
        ]);
    } */

    /* public function index2Action()
    {
        $content = "Bye Bye World !";
        return new Response($content);
    } */
    //-----------------------------------------------------------------

    // /**
    //  * Utiliser annotations pour definir les routes
    //  * @Route("/advert", name="oc_advert_index")
    //  */
    // public function indexAction($page)
    // {
        // $routes = new RouteCollection();
        // $routes->add('oc_advert_view', new RouteGen('/view/{id}'));

        // $context = new RequestContext('');
        // $generator = new UrlGenerator($routes, $context);

        // $url = $generator->generate(
        //     'oc_advert_view', // 1er argument : le nom de la route
        //     ['id' => 7]       // 2e argument : les paramètres
        // );

        // $url = $this->generateUrl(
        //     'oc_advert_view', // 1er argument : le nom de la route
        //     ['id' => 7]       // 2e argument : les paramètres
        // );
        // return new Response("L'URL de l'annonce d'id 7 est : ".$url);
    // }
    // ----------------------------------------------------------------


    // public function indexAction($page)
    // {
    //     return $this->render('@OCPlatform/Advert/index.html.twig', [
    //         'name' => 'Winzou'
    //     ]);
    // }


    // public function viewAction(Request $request, SessionInterface $session, $id)
    // {

        // $response = new Response();
        // $response->setContent("Ceci est une page d'erreur 404");
        // $response->setStatusCode(Response::HTTP_NOT_FOUND);
        // return $response;

        // $url = $this->generateUrl('oc_advert_index');
        // return new RedirectResponse($url);
        // return $this->redirect($url);
        // return $this->redirectToRoute('oc_advert_index');

        // Création de reponse en JSON avec json_encode()
        /* $response = new Response(json_encode(['id' => $id])); */
        //definition du Content-Type pr q la navig renvoir du JSON et non HTML
        /* $response->headers->set('Content-Type', 'application/json');
        return $response; */
        // return new JsonResponse(['id' => $id]);

        // $userId = $session->get('userId');  // recupère la var userId
        // $session->set('userId', 91);        // new valeur pr la var userId
        // return new Response("<body>Je suis une page de test, RAS</body>");
    // }

    // public function viewSlugAction($slug, $year, $_format)
    // {
    //     return new Response(
    //         "On pourrait afficher l'annonce correspondant au
    //         slug '".$slug."', créée en ".$year." et au format ".$_format."."
    //       );
    // }

// }