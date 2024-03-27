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

use App\Form\UserFormType;

class UserController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/signup', name: 'signup')]
    public function saveData(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserFormType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $username = $data->getUsername();
            $password = $data->getPassword();
            $hashedPassword = $passwordHasher->hashPassword($user, $password);
            $user->setPassword($hashedPassword);
            $user->setUsername($username);

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return $this->redirectToRoute('login_page');
        }

        return $this->render(
            'User/signup.html.twig',
            [
                'form' => $form,
            ]
        );
    }

    #[Route('/login', name: 'login')]
    public function login(Request $request, UserPasswordHasherInterface $passwordHasher, SessionInterface $session): Response
    {
        $form = $this->createForm(UserFormType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $username = $data->getUsername();
            $password = $data->getPassword();
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
        return $this->render(
            'User/login.html.twig',
            [
                'form' => $form,
            ]
        );
    }

    #[Route('/logout', name: 'logout', methods: ['GET'])]
    public function logout(Request $request, SessionInterface $session): Response
    {
        $session->remove('username');

        return $this->redirectToRoute('home');
    }
}