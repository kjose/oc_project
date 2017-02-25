<?php

// src/OC/PlatformBundle/Controller/AdvertController.php

namespace OC\PlatformBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use OC\PlatformBundle\Entity\Advert;
use OC\PlatformBundle\Entity\Image;
use OC\PlatformBundle\Entity\Application;
use OC\PlatformBundle\Entity\AdvertSkill;

class AdvertController extends Controller
{
  public function indexAction($page)
  {
    if ($page < 1) {
      throw new NotFoundHttpException('Page "'.$page.'" inexistante.');
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

    return $this->render('OCPlatformBundle:Advert:index.html.twig', array(
      'listAdverts' => $listAdverts,
    ));
  }

  public function viewAction($id)
  {

    $em = $this->getDoctrine()->getManager();

    // On récupère l'Advert
    $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);
    if ($advert == null) {
      throw new NotFoundHttpException('L\'annonce d\'id '.$id.' n\'existe pas.');
    }

    // Liste des applications
    $listApplications = $em->getRepository('OCPlatformBundle:Application')->findBy( array('advert'=>$advert ) );

    // On récupère maintenant la liste des AdvertSkill
    $listAdvertSkills = $em
      ->getRepository('OCPlatformBundle:AdvertSkill')
      ->findBy(array('advert' => $advert))
    ;

    return $this->render('OCPlatformBundle:Advert:view.html.twig', array(
      'advert' => $advert,
      'listApplications' => $listApplications,
      'listAdvertSkills' => $listAdvertSkills
    ));
  }

  public function addAction(Request $request)
  {

    // Récupération du manager
    $em = $this->getDoctrine()->getManager();

    // Création de l'entité
    $advert = new Advert();
    $advert->setTitle('Recherche développeur Symfony.');
    $advert->setAuthor('Kévin José');
    $advert->setContent('Nous recherchons un développeur Symfony débutant sur Lyon. Blabla…');
    $em->persist( $advert );

    // Création de l'image
    $image = new Image();
    $image->setUrl('http://sdz-upload.s3.amazonaws.com/prod/upload/job-de-reve.jpg');
    $image->setAlt('Job de rêve');
    $advert->setImage( $image );

    // On récupère toutes les compétences possibles
    $listSkills = $em->getRepository('OCPlatformBundle:Skill')->findAll();
    foreach( $listSkills as $skill ) {
      $advertSkill = new AdvertSkill();
      $advertSkill->setSkill( $skill );
      $advertSkill->setAdvert( $advert );
      $advertSkill->setLevel( 'Expert' );
      $em->persist( $advertSkill );
    }

    // Création d'une première candidature
    $application1 = new Application();
    $application1->setAuthor('Marine');
    $application1->setContent("J'ai toutes les qualités requises.");
    $application1->setAdvert($advert);
    $em->persist( $application1 );

    // Création d'une deuxième candidature par exemple
    $application2 = new Application();
    $application2->setAuthor('Pierre');
    $application2->setContent("Je suis très motivé.");
    $application2->setAdvert($advert);
    $em->persist( $application2 );

    // Flush commit code
    $em->flush();

    // Si la requête est en POST, c'est que le visiteur a soumis le formulaire
    if ($request->isMethod('POST')) {
      $request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');

      // Puis on redirige vers la page de visualisation de cettte annonce
      return $this->redirectToRoute('oc_platform_view', array('id' => $advert->getId() ));
    }

    // Si on n'est pas en POST, alors on affiche le formulaire
    return $this->redirectToRoute('oc_platform_view', array('id' => $advert->getId() ));
    // return $this->render('OCPlatformBundle:Advert:add.html.twig');
  }

  public function editAction($id, Request $request)
  {

    $em = $this->getDoctrine()->getManager();

    // On récupère l'Advert
    $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);
    if ($advert == null) {
      throw new NotFoundHttpException('L\'annonce d\'id '.$id.' n\'existe pas.');
    }

    // On ajoute les catégories à l'annonce
    $listCategories = $em->getRepository('OCPlatformBundle:Category')->findAll();
    // On boucle sur les catégories pour les lier à l'annonce
    foreach ($listCategories as $category) {
      $advert->addCategory($category);
    }
    $em->flush();

    if ($request->isMethod('POST')) {
      $request->getSession()->getFlashBag()->add('notice', 'Annonce bien modifiée.');

      return $this->redirectToRoute('oc_platform_view', array('id' => 5));
    }

    return $this->render('OCPlatformBundle:Advert:edit.html.twig', array(
      'advert' => $advert
    ));
  }

  public function deleteAction($id)
  {

    $em = $this->getDoctrine()->getManager();

    // On récupère l'Advert
    $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);
    if ($advert == null) {
      throw new NotFoundHttpException('L\'annonce d\'id '.$id.' n\'existe pas.');
    }

    // On boucle sur les catégories de l'annonce pour les supprimer
    foreach ($advert->getCategories() as $category) {
      $advert->removeCategory($category);
    }

    $em->flush();

    return $this->render('OCPlatformBundle:Advert:delete.html.twig');
  }

  public function menuAction($limit)
  {
    // On fixe en dur une liste ici, bien entendu par la suite on la récupérera depuis la BDD !
    $listAdverts = array(
      array('id' => 2, 'title' => 'Recherche développeur Symfony'),
      array('id' => 5, 'title' => 'Mission de webmaster'),
      array('id' => 9, 'title' => 'Offre de stage webdesigner')
    );

    return $this->render('OCPlatformBundle:Advert:menu.html.twig', array(
      // Tout l'intérêt est ici : le contrôleur passe les variables nécessaires au template !
      'listAdverts' => $listAdverts
    ));
  }
}
