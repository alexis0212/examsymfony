<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Client;
use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Repository\ArticleRepository;
use App\Repository\ClientRepository;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommandeController extends AbstractController
{
    #[Route('/commande', name: 'commande_index')]
public function index(
    Request $request,
    ClientRepository $clientRepository,
    ArticleRepository $articleRepository,
    CommandeRepository $commandeRepository,
    EntityManagerInterface $entityManager
): Response {
    $telephone = $request->query->get('telephone');
    $client = null;
    $commande = null;
    $articles = $articleRepository->findAll();

    if ($telephone) {
        $client = $clientRepository->findOneBy(['telephone' => $telephone]);

        if ($client) {
            // Vérifiez si une commande existe déjà pour ce client
            $commande = $commandeRepository->findOneBy(['client' => $client]);

            if (!$commande) {
                $commande = new Commande();
                $commande->setClient($client);
                $entityManager->persist($commande);
                $entityManager->flush();
            }
        } else {
            $this->addFlash('danger', 'Client non trouvé.');
        }
    }

    return $this->render('commande/index.html.twig', [
        'client' => $client,
        'commande' => $commande,
        'articles' => $articles,
    ]);
}

    

    #[Route('/commande/article/ajouter', name: 'commande_article_ajouter', methods: ['POST'])]
    public function ajouterArticle(
        Request $request,
        CommandeRepository $commandeRepository,
        ArticleRepository $articleRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $commandeId = $request->request->get('commandeId');
        $articleId = $request->request->get('article');
        $prix = $request->request->get('prix');
        $quantite = $request->request->get('quantite');

        $commande = $commandeRepository->find($commandeId);
        $article = $articleRepository->find($articleId);

        if ($commande && $article) {
            $ligneCommande = new LigneCommande();
            $ligneCommande->setCommande($commande);
            $ligneCommande->setArticle($article);
            $ligneCommande->setPrix($prix);
            $ligneCommande->setQuantite($quantite);

            $commande->addLigneCommande($ligneCommande);
            $commande->calculateTotal();

            $entityManager->persist($ligneCommande);
            $entityManager->persist($commande);
            $entityManager->flush();

            $this->addFlash('success', 'Article ajouté à la commande.');
        }

        return $this->redirectToRoute('commande_index', ['telephone' => $commande->getClient()->getTelephone()]);
    }

    #[Route('/commande/{id}/valider', name: 'commande_valider')]
    public function validerCommande(Commande $commande, EntityManagerInterface $entityManager): Response
    {
        $entityManager->persist($commande);
        $entityManager->flush();

        $this->addFlash('success', 'Commande validée avec succès.');

        return $this->redirectToRoute('commande_index', ['telephone' => $commande->getClient()->getTelephone()]);
    }
}
