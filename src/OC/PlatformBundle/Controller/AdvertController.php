<?php
namespace OC\PlatformBundle\Controller;

use OC\PlatformBundle\Entity\Image;
use OC\PlatformBundle\Entity\Advert;
use OC\PlatformBundle\Form\AdvertType;
use OC\PlatformBundle\Entity\AdvertSkill;
use OC\PlatformBundle\Entity\Application;
use OC\PlatformBundle\Form\AdvertEditType;
use OC\PlatformBundle\Event\PlatformEvents;
use OC\PlatformBundle\Event\MessagePostEvent;
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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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

    public function viewAction(Advert $advert, $id)
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

    // /**
    //  * 2- deuxième méthode pour vérifier l'autorisation user (2/4)
    //  * 
    //  * @Security("has_role('ROLE_AUTEUR')")
    //  */
    public function addAction(Request $request)
    {
        // 1-On vérifie si l'user dispose du rôle ROLE_AUTEUR
        // if (!$this->get('security.authorization_checker')->isGranted('ROLE_AUTEUR')) {
        //     // Sinon on déclenche une exception <<Accès Interdit>>
        //     throw new AccessDeniedException('Accès limité aux auteurs.');
        // }

        $advert = new Advert();
        $form = $this->createForm(AdvertType::class, $advert);
        //$form=$this->get('form.factory')->create(AdvertType::class,$advert);

        if ($request->isMethod('POST') && 
            $form->handleRequest($request)->isValid()) {
        /*    
            // On crée l'event avc ses 2 arguments
            $event = new MessagePostEvent($advert->getContent(), $advert->getUser());

            // On déclenche l'évent
            $this->get('event_dispatcher')->dispatch(PlatformEvents::POST_MESSAGE, $event);

            // On récupère ce qui a été modifié par les listeners (le msg)
            $advert->setContent($event->getMessage()); 
        */
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

        if (null === $advert) {
            throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
        }
        
        $form = $this->createForm(AdvertEditType::class, $advert);
       
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {

            $em->flush();
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


    public function deleteAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

        if (null === $advert) {
            throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
        }

        $form = $this->get('form.factory')->create();

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->remove($advert);
            $em->flush();

            $request->getSession()->getFlashBag()->add('notice', "L'annoce a bien été supprimée");
            return $this->redirectToRoute('oc_advert_home');
        }

        return $this->render('@OCPlatform/Advert/delete.html.twig', [
            'advert' => $advert,
            'form' => $form->createView()
        ]);
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

    public function translationAction($name)
    {
        return $this->render('@OCPlatform/Advert/translation.html.twig', [
            'name' => $name
        ]);
    }

    /**
     * @ParamConverter("json")
     */
    public function paramConverterAction($json)
    {
        return new Response(print_r($json, true));
    }


    // *** MES  TESTS *************************
    public function listAction()
    {
        // Exemple FOSUserBundle
        // Pour récupérer le service UserManager du bundle
        $userManager = $this->get('fos_user.user_manager');

        // Pour charger un utilisateur
        $user = $userManager->findUserBy(array('username' => 'winzou'));

        // Pour modifier un utilisateur
        $user->setEmail('cetemail@nexiste.pas');
        $userManager->updateUser($user); // Pas besoin de faire un flush avec l'EntityManager, cette méthode le fait toute seule !

        // Pour supprimer un utilisateur
        $userManager->deleteUser($user);

        // Pour récupérer la liste de tous les utilisateurs
        $users = $userManager->findUsers();


        $advert = new Advert;
        
        $advert->setDate(new \Datetime());  // Champ « date » OK
        $advert->setTitle('Recherche de developpeur Symfony 3');            // Champ « title » incorrect : moins de 10 caractères
        $advert->setContent('abandon dept');    // Champ « content » incorrect : on ne le définit pas ou Contient un "mot interdit"
        $advert->setAuthor('A');            // Champ « author » incorrect : moins de 2 caractères
            
        // On récupère le service validator
        $validator = $this->get('validator');
            
        // On déclenche la validation sur notre object
        $listErrors = $validator->validate($advert);

        // Si $listErrors n'est pas vide, on affiche les erreurs
        if(count($listErrors) > 0) {
            // $listErrors est un objet, sa méthode __toString permet de lister joliement les erreurs
            return new Response((string) $listErrors);
        } else {
            return new Response("L'annonce est valide !");
        }
    }

}
