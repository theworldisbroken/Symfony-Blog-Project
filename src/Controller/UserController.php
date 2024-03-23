<?php
namespace App\Controller;

use Symfony\Component\Security\Core\Exception\BadCredentialsException;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class UserController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/signup', name: 'user-signup', methods: ['GET'])]
    public function showPage(Request $request): Response
    {
        return $this->render('User/signup.html.twig');
    }

    #[Route('/signup', name: 'signup', methods: ['POST'])]
    public function saveData(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $username = $request->request->get('username');
        $password = $request->request->get('password');

        $user = new User();

        $user->setUsername($username);

        $hashedPassword = $passwordHasher->hashPassword($user, $password);

        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // $users = $this->entityManager->getRepository(User::class)->findAll();

        return $this->redirectToRoute('login_page');
    }


    #[Route('/login', name: 'login_page', methods: ['GET'])]
    public function loginPage(Request $request): Response
    {
        return $this->render('User/login.html.twig');
    }

    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(Request $request, UserPasswordHasherInterface $passwordHasher, SessionInterface $session): Response
    {
        $username = $request->request->get('username');
        $password = $request->request->get('password');

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $username]);

        if (!$user || !$passwordHasher->isPasswordValid($user, $password)) {
            return $this->render('User/login.html.twig', [
                'error' => true
            ]);
        } else {
            $session->set('username', $username);
            return $this->redirectToRoute('personalpage');
        }
    }

    #[Route('/logout', name: 'logout', methods: ['GET'])]
    public function logout(Request $request, SessionInterface $session): Response
    {
        $session->remove('username');

        return $this->redirectToRoute('home');
    }
}