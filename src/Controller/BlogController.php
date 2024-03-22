<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Entity\Blog;

class BlogController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'home')]
    public function homePage(Request $request, SessionInterface $session): Response
    {
        $username = $session->get('username');
        $articles = $this->entityManager->getRepository(Blog::class)->findAll();

        return $this->render('base.html.twig', [
            'articles' => $articles,
            'username' => $username
        ]);
    }

    #[Route('/create', name: 'create_GET', methods: ['GET'])]
    public function getCraeteArticle(SessionInterface $session): Response
    {
        $username = $session->get('username');

        if (!$username) {
            return $this->redirectToRoute('login');
        }

        return $this->render('blog/createArticle.html.twig', [
            'username' => $username,
        ]);
    }

    #[Route('/create', name: 'create_article', methods: ['POST'])]
    public function craeteArticle(Request $request, SessionInterface $session): Response
    {
        $username = $session->get('username');
        $title = $request->request->get('title');
        $body = $request->request->get('body');

        if (!$username) {
            return $this->redirectToRoute('login');
        }

        if(!$title || !$body) {
            return $this->render('blog/createArticle.html.twig', [
                'error' => true
            ]);
        }

        $article = new Blog();
        $article->setTitle($title);
        $article->setBody($body);
        $article->setUsername($username);

        $this->entityManager->persist($article);
        $this->entityManager->flush();

        return $this->redirectToRoute('home');
    }
}
