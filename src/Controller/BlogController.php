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
    public function homePage(Request $request): Response
    {
        $username = '';
        $sessionUsername = $this->getUser();
        if ($sessionUsername) {
            $username = $this->getUser()->getUsername();
        }
        
        $articles = $this->entityManager->getRepository(Blog::class)->findAll();

        return $this->render('base.html.twig', [
            'articles' => $articles,
            'username' => $username
        ]);
    }

    #[Route('/create', name: 'create_GET')]
    public function getCraeteArticle(Request $request, SessionInterface $session): Response
    {
        $username = $this->getUser()->getUsername();

        if (!$username) {
            return $this->redirectToRoute('app_login');
        }

        $article = new Blog();
        $form = $this->createForm(CreatePostType::class, $article, ['attr' => ['class' => 'create-article-form']]);
        $article->setUsername($username);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $picture = $form->get('picture')->getData();

            if ($picture) {
                $newFilename = uniqid() . '.' . $picture->guessExtension();

                try {
                    $picture->move(
                        $this->getParameter('artilces_pics'),
                        $newFilename
                    );
                } catch (FileException $e) {
                }

                $article->setPicture($newFilename);
            }
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
    public function getArticle(Request $request): Response
    {
        $id = $request->get('id');
        $username = $this->getUser()->getUsername();
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
    public function editArticle(Request $request): Response
    {
        $username = $this->getUser()->getUsername();
        $id = $request->get('id');
        $title = $request->request->get('title');
        $body = $request->request->get('body');

        if (!$username) {
            return $this->redirectToRoute('app_login');
        }

        $article = $this->entityManager->getRepository(Blog::class)->find($id);
        $article->setTitle($title);
        $article->setBody($body);

        $this->entityManager->flush();

        return $this->redirectToRoute('article', ['id' => $id]);
    }

    #[Route('/article/delete/{id}', name: 'article_delete', methods: ['GET'])]
    public function deleteArticle(Request $request): Response
    {
        $id = $request->get('id');
        $username = $this->getUser()->getUsername();
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

    #[Route('/personalpage', name: 'personalpage')]
    public function personalpage(Request $request): Response
    {
        $username = $this->getUser()->getUserIdentifier();
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
