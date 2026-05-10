<?php

namespace App\Controller;

use App\Entity\JobPosting;
use App\Repository\JobPostingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ApplicationRepository;

#[Route('/api/jobs')]
class JobPostingController extends AbstractController
{
    #[Route('', name: 'jobs_list', methods: ['GET'])]
    public function index(JobPostingRepository $repo): JsonResponse
    {
        $jobs = $repo->findBy(['status' => 'open']);
        return $this->json(array_map(fn($j) => $this->serialize($j), $jobs));
    }

    #[Route('/{id}', name: 'jobs_show', methods: ['GET'])]
    public function show(JobPosting $job): JsonResponse
    {
        return $this->json($this->serialize($job));
    }

    #[Route('', name: 'jobs_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        if ($user->getRole() !== 'employer') {
            return $this->json(['error' => 'Only employers can post jobs'], 403);
        }

        $data = json_decode($request->getContent(), true);
        if (!isset($data['title'], $data['description'], $data['location'])) {
            return $this->json(['error' => 'Missing required fields'], 400);
        }

        $job = new JobPosting();
        $job->setTitle($data['title']);
        $job->setDescription($data['description']);
        $job->setLocation($data['location']);
        $job->setSalary($data['salary'] ?? null);
        $job->setStatus('open');
        $job->setEmployer($user);
        $job->setCreatedAt(new \DateTimeImmutable());

        $em->persist($job);
        $em->flush();

        $this->addFlash('success', 'Job posted successfully!');
        return $this->json($this->serialize($job), 201);
    }

    #[Route('/{id}', name: 'jobs_update', methods: ['PUT'])]
    public function update(JobPosting $job, Request $request, EntityManagerInterface $em): JsonResponse
    {
        if ($job->getEmployer() !== $this->getUser()) {
            return $this->json(['error' => 'Access denied'], 403);
        }

        $data = json_decode($request->getContent(), true);
        if (isset($data['title'])) $job->setTitle($data['title']);
        if (isset($data['description'])) $job->setDescription($data['description']);
        if (isset($data['location'])) $job->setLocation($data['location']);
        if (isset($data['salary'])) $job->setSalary($data['salary']);
        if (isset($data['status'])) $job->setStatus($data['status']);

        $em->flush();
        return $this->json($this->serialize($job));
    }

    #[Route('/{id}', name: 'jobs_delete', methods: ['DELETE'])]
    public function delete(JobPosting $job, EntityManagerInterface $em): JsonResponse
    {
        if ($job->getEmployer() !== $this->getUser()) {
            return $this->json(['error' => 'Access denied'], 403);
        }

        $em->remove($job);
        $em->flush();
        return $this->json(['message' => 'Job deleted']);
    }
    #[Route('/mine', name: 'jobs_mine', methods: ['GET'])]
    public function mine(JobPostingRepository $repo, ApplicationRepository $appRepo): JsonResponse
    {
        $user = $this->getUser();
        if ($user->getRole() !== 'employer') {
            return $this->json(['error' => 'Access denied'], 403);
        }

        $jobs = $repo->findBy(['employer' => $user]);
        return $this->json(array_map(fn($j) => [
            ...$this->serialize($j),
            'applicationCount' => count($appRepo->findBy(['jobPosting' => $j])),
        ], $jobs));
    }

    private function serialize(JobPosting $job): array
    {
        return [
            'id' => $job->getId(),
            'title' => $job->getTitle(),
            'description' => $job->getDescription(),
            'location' => $job->getLocation(),
            'salary' => $job->getSalary(),
            'status' => $job->getStatus(),
            'employer' => $job->getEmployer()->getEmail(),
            'createdAt' => $job->getCreatedAt()->format('c'),
        ];
    }
}