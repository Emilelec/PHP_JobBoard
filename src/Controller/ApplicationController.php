<?php

namespace App\Controller;

use App\Entity\Application;
use App\Repository\ApplicationRepository;
use App\Repository\JobPostingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
class ApplicationController extends AbstractController
{
    #[Route('/jobs/{id}/apply', name: 'job_apply', methods: ['POST'])]
    public function apply(
        int $id,
        JobPostingRepository $jobRepo,
        ApplicationRepository $appRepo,
        EntityManagerInterface $em
    ): JsonResponse {
        $user = $this->getUser();
        if ($user->getRole() !== 'candidate') {
            return $this->json(['error' => 'Only candidates can apply'], 403);
        }

        $job = $jobRepo->find($id);
        if (!$job || $job->getStatus() !== 'open') {
            return $this->json(['error' => 'Job not found or closed'], 404);
        }

        $existing = $appRepo->findOneBy(['jobPosting' => $job, 'candidate' => $user]);
        if ($existing) {
            return $this->json(['error' => 'Already applied to this job'], 409);
        }

        $application = new Application();
        $application->setJobPosting($job);
        $application->setCandidate($user);
        $application->setStatus('pending');
        $application->setCreatedAt(new \DateTimeImmutable());

        $em->persist($application);
        $em->flush();

        $this->addFlash('success', 'Application submitted successfully!');
        return $this->json([
            'id' => $application->getId(),
            'job' => $job->getTitle(),
            'status' => $application->getStatus(),
            'createdAt' => $application->getCreatedAt()->format('c'),
        ], 201);
    }

    #[Route('/applications', name: 'my_applications_api', methods: ['GET'])]
    public function myApplications(ApplicationRepository $appRepo): JsonResponse
    {
        $user = $this->getUser();

        if ($user->getRole() !== 'candidate') {
            return $this->json(['error' => 'Access denied'], 403);
        }

        $applications = $appRepo->findBy(['candidate' => $user]);

        return $this->json(array_map(fn($a) => [
            'id' => $a->getId(),
            'job' => $a->getJobPosting()->getTitle(),
            'location' => $a->getJobPosting()->getLocation(),
            'employer' => $a->getJobPosting()->getEmployer()->getEmail(),
            'status' => $a->getStatus(),
            'createdAt' => $a->getCreatedAt()->format('c'),
        ], $applications));
    }

    #[Route('/jobs/{id}/applications', name: 'job_applications_api', methods: ['GET'])]
    public function jobApplications(
        int $id,
        JobPostingRepository $jobRepo,
        ApplicationRepository $appRepo
    ): JsonResponse {
        $user = $this->getUser();
        $job = $jobRepo->find($id);

        if (!$job) {
            return $this->json(['error' => 'Job not found'], 404);
        }

        if ($job->getEmployer() !== $user) {
            return $this->json(['error' => 'Access denied'], 403);
        }

        $applications = $appRepo->findBy(['jobPosting' => $job]);
        return $this->json(array_map(fn($a) => [
            'id' => $a->getId(),
            'candidate' => $a->getCandidate()->getEmail(),
            'status' => $a->getStatus(),
            'createdAt' => $a->getCreatedAt()->format('c'),
        ], $applications));
    }
}