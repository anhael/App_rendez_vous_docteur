<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class AuthController extends AbstractController
{
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(
        Request $request,
        UserRepository $userRepository,
        UserPasswordHasherInterface $hasher,
        JWTTokenManagerInterface $jwtManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true) ?: [];
        $email = trim((string) ($data['email'] ?? ''));
        $password = (string) ($data['password'] ?? '');

        if ($email === '' || $password === '') {
            return $this->json(['message' => 'Missing credentials'], 400);
        }

        $user = $userRepository->findOneBy(['email' => $email]);

        if (!$user instanceof User || !$hasher->isPasswordValid($user, $password)) {
            return $this->json(['message' => 'Invalid credentials'], 401);
        }

        return $this->json([
            'token' => $jwtManager->create($user),
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'name' => $user->getName(),
                'firstname' => $user->getFirstname(),
                'roles' => $user->getRoles(),
            ],
        ]);
    }

    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher
    ): JsonResponse {

        $data = json_decode($request->getContent(), true) ?: [];

        if (!isset($data['email'], $data['password'], $data['name'], $data['firstname'])) {
            return $this->json(['error' => 'Champs requis manquants'], 400);
        }

        $existingUser = $em->getRepository(User::class)->findOneBy([
            'email' => $data['email']
        ]);

        if ($existingUser) {
            return $this->json(['error' => 'Email deja utilise'], 409);
        }

        $user = new User();
        $user->setEmail($data['email']);
        $user->setName($data['name']);
        $user->setFirstname($data['firstname']);
        $user->setPhone(isset($data['phone']) ? trim((string) $data['phone']) : null);

        $dateOfBirthRaw = trim((string) ($data['date_of_birth'] ?? ''));
        if ($dateOfBirthRaw !== '') {
            $dateOfBirth = \DateTime::createFromFormat('Y-m-d', $dateOfBirthRaw);
            if (!$dateOfBirth instanceof \DateTime) {
                return $this->json(['error' => 'Format date de naissance invalide'], 400);
            }
            $user->setDateOfBirth($dateOfBirth);
        }

        $hashedPassword = $hasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);

        $user->setRoles(['ROLE_USER']);

        $em->persist($user);
        $em->flush();

        return $this->json([
            'message' => 'Compte cree avec succes'
        ]);
    }

    // GET PROFILE (optionnel mais utile)
    #[Route('/api/me', name: 'api_me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        return $this->json([
            'email' => $user->getEmail(),
            'name' => $user->getName(),
            'firstname' => $user->getFirstname(),
            'phone' => $user->getPhone(),
            'date_of_birth' => $user->getDateOfBirth()?->format('Y-m-d'),
            'roles' => $user->getRoles(),
        ]);
    }
}
