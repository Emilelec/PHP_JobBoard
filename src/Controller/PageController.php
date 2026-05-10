<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class PageController extends AbstractController
{
    #[Route('/login', name: 'login_page')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        return $this->render('login.html.twig', [
            'error' => $authenticationUtils->getLastAuthenticationError(),
            'last_username' => $authenticationUtils->getLastUsername(),
        ]);
    }

    #[Route('/', name: 'home', methods: ['GET'])]
    public function home(): Response
    {
        return $this->redirectToRoute('jobs_page');
    }

    #[Route('/jobs', name: 'jobs_page')]
    public function jobs(): Response
    {
        return $this->render('jobs.html.twig');
    }

    #[Route('/register', name: 'register_page')]
    public function register(): Response
    {
        return $this->render('register.html.twig');
    }

    #[Route('/my-applications', name: 'my_applications_page')]
    public function myApplications(): Response
    {
        if ($this->getUser()->getRole() !== 'candidate') {
            return $this->redirectToRoute('jobs_page');
        }
        return $this->render('my_applications.html.twig');
    }

    #[Route('/dashboard', name: 'dashboard_page')]
    public function dashboard(): Response
    {
        if ($this->getUser()->getRole() !== 'employer') {
            return $this->redirectToRoute('jobs_page');
        }
        return $this->render('dashboard.html.twig');
    }
}