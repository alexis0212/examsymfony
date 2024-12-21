<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\Client;
use App\Entity\Commande;
use App\Entity\LigneCommande;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Ajouter des articles
        $articles = [];
        for ($i = 1; $i <= 5; $i++) {
            $article = new Article();
            $article->setNom("Article $i");
            $article->setPrixUnitaire(mt_rand(10, 100)); // Prix entre 10 et 100
            $manager->persist($article);
            $articles[] = $article;
        }

        // Ajouter des clients
        $clients = [];
        for ($i = 1; $i <= 3; $i++) {
            $client = new Client();
            $client->setNom("Client $i");
            $client->setPrenom("Prenom $i");
            $client->setTelephone("77000000$i");
            $client->setVille("Ville $i");
            $client->setQuartier("Quartier $i");
            $client->setNumeroVilla("Villa $i");
            $manager->persist($client);
            $clients[] = $client;
        }

        // Ajouter des commandes
        foreach ($clients as $client) {
            for ($j = 1; $j <= 2; $j++) {
                $commande = new Commande();
                $commande->setClient($client);

                // Ajouter des lignes de commande à la commande
                for ($k = 1; $k <= 3; $k++) {
                    $ligneCommande = new LigneCommande();
                    $article = $articles[array_rand($articles)];
                    $prix = $article->getPrixUnitaire();
                    $quantite = mt_rand(1, 5);

                    $ligneCommande->setArticle($article);
                    $ligneCommande->setPrix($prix);
                    $ligneCommande->setQuantite($quantite);
                    $ligneCommande->setCommande($commande);

                    $commande->addLigneCommande($ligneCommande);
                    $manager->persist($ligneCommande);
                }

                // Calculer le total de la commande
                $commande->calculateTotal();
                $manager->persist($commande);
            }
        }

        // Sauvegarder tout dans la base de données
        $manager->flush();
    }
}
