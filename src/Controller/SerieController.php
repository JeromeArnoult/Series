<?php

namespace App\Controller;

use App\Entity\Serie;
use App\Form\SerieType;
use App\Repository\SerieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SerieController extends AbstractController
{
    #[Route('/series', name: 'serie_list')]
    public function list(SerieRepository $serieRepository): Response
    {
        //L'utilisation du find by permet d'effectuer un trie
        $series = $serieRepository->findBestSeries();

        return $this->render('serie/list.html.twig',["series" => $series]);
    }

    #[Route('/series/details/{id}', name: 'serie_details')]
    public function details (int $id, SerieRepository $serieRepository): Response
    {
        $serie = $serieRepository->find($id);

        if(!$serie) {
            throw $this->createNotFoundException('oh no!!!');
        }

        return $this->render('serie/details.html.twig',["serie" => $serie]);
    }

    #[Route('/series/create', name: 'serie_create')]
    public function create (
        Request $request,
        EntityManagerInterface $entityManager
    ): Response
    {
        $serie = new Serie();
        //Pour ajouter la date de création de la serie automatiquement à la création
        $serie->setDateCreated(new \DateTime());
        $serieForm = $this->createForm(SerieType::class, $serie);

        $serieForm->handleRequest($request);

        if ($serieForm ->isSubmitted() && $serieForm->isValid()) {
            //Pour "persister" les données en base de données
            $entityManager->persist($serie);

            $this->addFlash('success','Serie added! Good job.');
            return $this->redirectToRoute('serie_details',['id' => $serie->getId()]);
        }
        $entityManager->flush();

        return $this->render('serie/create.html.twig', ['serieForm' => $serieForm->createView()
        ]);
    }

    #[Route('/series/demo', name: 'serie_em-demo')]
    //Classe de demo pour inserer des données en base a partir de la function, on declare l'entityManager en argument
    public function demo (EntityManagerInterface $entityManager): Response
    {
        //Création de l'instance de l'entité
        $serie = new Serie();

        //hydrate toutes les propriétés
        $serie->setName('pif');
        $serie->setBackdrop('dafsd');
        $serie->setPoster('dafsdfd');
        $serie->setDateCreated(new \DateTime());
        $serie->setFirstAirDate(new \DateTime("- 1 year"));
        $serie->setLastAirDate(new \DateTime("- 6 month"));
        $serie->setGenres('drama');
        $serie->setPopularity(123.00);
        $serie->setVote(8.2);
        $serie->setStatus("Canceled");
        $serie->setTmdbId(329432);

        dump($serie);
        // Et on insert les données de mon objet serie en base
        $entityManager->persist($serie);
        $entityManager->flush();

        dump($serie);

        //Pour modifier un attribut:
        $serie->setGenres('comedy');
        $entityManager->flush();

        //Supprimer mon object serie !!
        $entityManager->remove($serie);
        $entityManager->flush();
        return $this->render('serie/demo.html.twig');
    }


    #[Route('series/delete/{id}', name: 'delete')]
    public function delete(Serie $serie, EntityManagerInterface $entityManager) {

        $entityManager->remove($serie);
        $entityManager->flush();

        return $this->redirectToRoute('main_home');

    }
}
