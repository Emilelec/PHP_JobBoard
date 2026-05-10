<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api')]
class AuthController extends AbstractController
{
    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $hasher,
        EntityManagerInterface $em,
        ValidatorInterface $validator
        ): JsonResponse|Response {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['email'], $data['password'], $data['role'])) {
                return $this->json(['error' => 'Missing fields'], 400);
            }

            if (!in_array($data['role'], ['candidate', 'employer'])) {
                return $this->json(['error' => 'Role must be candidate or employer'], 400);
            }

            $user = new User();
            $user->setEmail($data['email']);
            $user->setRole($data['role']);
            $user->setPassword($hasher->hashPassword($user, $data['password']));

            $errors = $validator->validate($user);
            if (count($errors) > 0) {
                return $this->json(['error' => (string) $errors], 400);
            }

            $em->persist($user);
            $em->flush();
    
        // if request came from browser form, redirect with flash
        if ($request->getContentTypeFormat() !== 'json') {
            $this->addFlash('success', 'Account created! You can now log in.');
            return $this->redirectToRoute('login_page');
        }

        return $this->json(['message' => 'User created', 'email' => $user->getEmail()], 201);
    }
}