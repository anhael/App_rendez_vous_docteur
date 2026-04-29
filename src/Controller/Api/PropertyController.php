<?php

namespace App\Controller\Api;

use App\Entity\Clinic;
use App\Entity\Doctor;
use App\Entity\DoctorAvailability;
use App\Repository\ClinicRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class PropertyController extends AbstractController
{
    #[Route('/api/clinics', name: 'api_clinics', methods: ['GET'])]
    public function index(ClinicRepository $clinicRepository): JsonResponse
    {
        $clinics = $clinicRepository->findBy([], ['name' => 'ASC']);

        return $this->json(array_map(fn(Clinic $clinic) => [
            'id' => $clinic->getId(),
            'name' => $clinic->getName(),
            'address' => $clinic->getAddress(),
            'phone' => $clinic->getPhone(),
            'doctors' => array_map(fn(Doctor $doctor) => [
                'id' => $doctor->getId(),
                'fullName' => $doctor->getFullName(),
                'specialty' => $doctor->getSpecialty(),
                'phone' => $doctor->getPhone(),
                'availabilities' => array_map(fn(DoctorAvailability $availability) => [
                    'id' => $availability->getId(),
                    'weekday' => $availability->getWeekday(),
                    'startTime' => $availability->getStartTime()?->format('H:i'),
                    'endTime' => $availability->getEndTime()?->format('H:i'),
                ], $doctor->getAvailabilities()->toArray()),
            ], $clinic->getDoctors()->toArray()),
        ], $clinics));
    }
}
