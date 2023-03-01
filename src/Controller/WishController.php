<?php

namespace App\Controller;

use App\Entity\Wish;
use App\Form\WishType;
use App\Repository\CategoryRepository;
use App\Repository\WishRepository;
use App\Utils\Censurator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/wish', name: 'wish_')]
class WishController extends AbstractController
{
    #[Route('/list', name: 'list')]
    public function list(WishRepository $wishRepository, CategoryRepository $repository): Response
    {
        //récupérer la liste des wish et la renvoyer
        //$wishes = $wishRepository->findAll();
        //$wishes= $wishRepository->findBy([], ["dateCreated"=>"DESC"]);
        $wishes = $wishRepository->findPublishWishes();

        return $this->render('wish/list.html.twig', ['wishes'=> $wishes]);
    }

    #[Route('/{id}', name: 'show', requirements: ['id'=>'\d+'])]
    public function detail( WishRepository $wishRepository, int $id): Response
    {
        //récupérer le wish et le renvoyer
        $wish = $wishRepository->find($id);

        if (!$wish){
            //lance une erreur 404 si la série n'existe pas
            throw $this->createNotFoundException("Oops ! Serie not found !");
        }

        return $this->render('wish/show.html.twig', ['wish'=> $wish]);
    }

    #[Route('/add', name: 'add')]
    #[IsGranted("ROLE_USER")]
    public function add( WishRepository $wishRepository, Request $request, Censurator $censurator): Response
    {

        $wish = new Wish();

        $wish->setAuthor($this->getUser()->getUserIdentifier());

        //création d'une instance de form lié à une instance de série
        $wishForm = $this->createForm(WishType::class, $wish);

        //méthode qui extrait les éléments du formulaire de la requête
        $wishForm->handleRequest($request);

        if ($wishForm->isSubmitted() && $wishForm->isValid()) {
            $wish->setDescription($censurator->purify($wish->getDescription()));
            $wish->setTitle($censurator->purify($wish->getTitle()));
            //sauvegarde en BDD
            $wishRepository->save($wish, true);

            $this->addFlash("success", "Idea successfully added !");

            //redirige vers la page de détail de la série
            return $this->redirectToRoute('wish_show', ['id' => $wish->getId()]);

        }

        return $this->render('wish/add.html.twig', [
            'wishForm'=>$wishForm->createView()

        ]);

    }

    #[Route('/update/{id}', name: 'update', requirements: ['id'=>'\d+'])]
    public function update( WishRepository $wishRepository, int $id): Response
    {
        //récupérer le wish et le renvoyer
        $wish = $wishRepository->find($id);

        if (!$wish){
            //lance une erreur 404 si la série n'existe pas
            throw $this->createNotFoundException("Oops ! Serie not found !");
        }


        $wishForm = $this->createForm(WishType::class, $wish);

        return $this->render('wish/update.html.twig', [
            'wish'=> $wish,
            'wishForm'=> $wishForm->createView()
            ]);
    }

}
