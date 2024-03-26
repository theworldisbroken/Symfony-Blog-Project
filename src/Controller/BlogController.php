<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Entity\Blog;

use App\Form\CreatePostType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;


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
    public function getCraeteArticle(Request $request, SessionInterface $session): Response
    {
        $username = $session->get('username');

        if (!$username) {
            return $this->redirectToRoute('login');
        }

        $article = new Blog();
        $form = $this->createForm(CreatePostType::class, $article, ['attr' => ['class' => 'create-article-form']]);
        $article->setUsername($username);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
          
            $article->setTitle($data->getTitle());
            $article->setBody($data->getBody());

            $this->entityManager->persist($article);
            $this->entityManager->flush();

            return $this->redirectToRoute('home');
        }

        return $this->render('blog/createArticle.html.twig', [
            'username' => $username,
            'form' => $form->createView()
        ]);
    }


    #[Route('/article/{id}', name: 'article', methods: ['GET'])]
    public function getArticle(Request $request, SessionInterface $session): Response
    {
        $id = $request->get('id');
        $username = $session->get('username');
        if (!$id) {
            return $this->redirectToRoute('home');
        }

        $article = $this->entityManager->getRepository(Blog::class)->find($id);

        return $this->render('blog/article.html.twig', [
            'article' => $article,
            'username' => $username
        ]);
    }

    #[Route('/article/edit/{id}', name: 'edit_article', methods: ['POST'])]
    public function editArticle(Request $request, SessionInterface $session): Response
    {
        $username = $session->get('username');
        $id = $request->get('id');
        $title = $request->request->get('title');
        $body = $request->request->get('body');

        if (!$username) {
            return $this->redirectToRoute('login');
        }

        $article = $this->entityManager->getRepository(Blog::class)->find($id);
        $article->setTitle($title);
        $article->setBody($body);

        $this->entityManager->flush();

        return $this->redirectToRoute('article', ['id' => $id]);
    }

    #[Route('/article/delete/{id}', name: 'article_delete', methods: ['GET'])]
    public function deleteArticle(Request $request, SessionInterface $session): Response
    {
        $id = $request->get('id');
        $username = $session->get('username');
        if (!$id) {
            return $this->redirectToRoute('home');
        }

        $article = $this->entityManager->getRepository(Blog::class)->find($id);
        if ($article->getUsername() === $username) {
            $this->entityManager->remove($article);
            $this->entityManager->flush();
            return $this->redirectToRoute('home');
        }
        return $this->redirectToRoute('home');
    }

    #[Route('/personalpage', name: 'personalpage', methods: ['GET'])]
    public function personalpage(Request $request, SessionInterface $session): Response
    {
        $username = $session->get('username');
        if (!$username) {
            return $this->redirectToRoute('login');
        }
        $articles = $this->entityManager->getRepository(Blog::class)->findBy(["username" => $username]);
        return $this->render(
            'Logged_in/personalpage.html.twig',
            [
                'username' => $username,
                'articles' => $articles
            ]
        );
    }
}
