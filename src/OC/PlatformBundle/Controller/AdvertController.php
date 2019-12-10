<?php
namespace OC\PlatformBundle\Controller;

use OC\PlatformBundle\Entity\Image;
use OC\PlatformBundle\Entity\Advert;
use OC\PlatformBundle\Entity\AdvertSkill;
use OC\PlatformBundle\Entity\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AdvertController extends Controller
{
    public function indexAction($page)
    {
        if ($page < 1) {
            throw $this->createNotFoundException('page "'.$page.'" inexistante.');
        }
        $nbPerPage = 3;
        $listAdverts = $this->getDoctrine()->getManager()
                            ->getRepository('OCPlatformBundle:Advert')
                            ->getAdverts($page, $nbPerPage);
        
        $nbPages = ceil(count($listAdverts) / $nbPerPage);

        if ($page > $nbPages) {
            throw $this->createNotFoundException('page "'.$page.'" inexistante.');
        }

        return $this->render('@OCPlatform/Advert/index.html.twig', [
            'listAdverts' => $listAdverts,
            'nbPages' => $nbPages,
            'page' => $page
        ]);
    }

    public function viewAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        // On récupère l'annonce $id
        $advert = $em->getRepository('OCPlatformBundle:Advert')
                     ->find($id);

        if (null === $advert) {
            throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
        }

        $listApplications = $em->getRepository('OCPlatformBundle:Application')
                               ->findBy(array('advert' => $advert));

        // On récupère les AdvertSkill de l'annonce
        $listAdvertSkills = $em->getRepository('OCPlatformBundle:AdvertSkill')
                               ->findBy(array('advert' => $advert));

        return $this->render('@OCPlatform/Advert/view.html.twig', [
            'advert'           => $advert,
            'listApplications' => $listApplications,
            'listAdvertSkills' => $listAdvertSkills
            ]);
    }

    public function addAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        // Pas de formulaire pour l'instant
        if ($request->isMethod('POST')) {
            $request->getSession()->getFlashBag()->add('notice', 'Annonce bien modifiée.');

            return $this->redirectToRoute('oc_platform_view', [
                'id' => $advert->getId()
            ]);
        }
        
        return $this->render('@OCPlatform/Advert/add.html.twig');
    }


    public function editAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

        if (null === $advert) {
            throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
        }
        
        // Pas de formulaire
        if ($request->isMethod('POST')) {
            $this->addFlash('notice', 'Annonce bien modifiée.');
            
            return $this->redirectToRoute('oc_advert_view', [
                'id' => $advert->getId()
            ]);
        }

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

        $em->flush();

        return $this->render('@OCPlatform/Advert/delete.html.twig');
    }

    public function menuAction($limit)
    {
        $em = $this->getDoctrine()->getManager();

        $listAdverts = $em->getRepository('OCPlatformBundle:Advert')->findBy(
            array(),
            array('date' => 'DESC'),
            $limit,
            0
        );
  
      return $this->render('@OCPlatform/Advert/menu.html.twig', array(
        'listAdverts' => $listAdverts
      ));
    }


    public function viewSlugAction($slug, $year, $_format)
    {
        return new Response(
            "On pourrait afficher l'annonce correspondant au
            slug '".$slug."', créée en ".$year." et au format ".$_format."."
          );
    }


    // *** MES  TESTS *************************
    public function listAction()
    {
        $listAdverts = $this
            ->getDoctrine()->getManager()
            ->getRepository('OCPlatformBundle:Advert')
            // ->getAdvertWithCategories(['Graphisme', 'Réseau']);
            ->getAdvertsWithSkill();

        return $this->render('@OCPlatform/Advert/test.html.twig', [
            'listAdverts' => $listAdverts
        ]);
    }

}
