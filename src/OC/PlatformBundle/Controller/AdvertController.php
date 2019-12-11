<?php
namespace OC\PlatformBundle\Controller;

use OC\PlatformBundle\Entity\Image;
use OC\PlatformBundle\Entity\Advert;
use OC\PlatformBundle\Form\AdvertType;
use OC\PlatformBundle\Entity\AdvertSkill;
use OC\PlatformBundle\Entity\Application;
use OC\PlatformBundle\Form\AdvertEditType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
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
        $advert = new Advert();
        $form = $this->createForm(AdvertType::class, $advert);
        //$form=$this->get('form.factory')->create(AdvertType::class,$advert);

        if ($request->isMethod('POST') && 
            $form->handleRequest($request)->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($advert);
            $em->flush();
            $request->getSession()->getFlashBag()
                    ->add('notice', 'Annonce bien enregistrée.');
                
            return $this->redirectToRoute('oc_advert_view', [
                'id' => $advert->getId()
            ]);
        }

        return $this->render('@OCPlatform/Advert/add.html.twig', [
            'form' => $form->createView()
        ]);
    }


    public function editAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);
        $form = $this->createForm(AdvertEditType::class, $advert);

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
            'form' => $form->createView(),
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

        $em->remove($advert);   // Suppression de l'objet recherché
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
