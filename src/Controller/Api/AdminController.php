<?php

namespace App\Controller\Api;

use App\Entity\Appointment;
use App\Entity\Clinic;
use App\Entity\Doctor;
use App\Entity\DoctorAvailability;
use App\Entity\User;
use App\Repository\AppointmentRepository;
use App\Repository\ClinicRepository;
use App\Repository\DoctorRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/admin')]
class AdminController extends AbstractController
{
    #[Route('/dashboard', name: 'api_admin_dashboard', methods: ['GET'])]
    public function dashboard(
        ClinicRepository $clinicRepository,
        DoctorRepository $doctorRepository,
        AppointmentRepository $appointmentRepository,
        UserRepository $userRepository
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $clinics = $clinicRepository->findBy([], ['id' => 'DESC']);
        $doctors = $doctorRepository->findBy([], ['id' => 'DESC']);
        $appointments = $appointmentRepository->findBy([], ['appointmentAt' => 'DESC']);
        $users = $userRepository->findBy([], ['id' => 'DESC']);

        return $this->json([
            'stats' => [
                'clinics' => $clinicRepository->count([]),
                'doctors' => $doctorRepository->count([]),
                'appointments' => $appointmentRepository->count([]),
                'patients' => $userRepository->count([]),
            ],
            'clinics' => array_map(fn(Clinic $clinic) => $this->serializeClinic($clinic), $clinics),
            'doctors' => array_map(fn(Doctor $doctor) => $this->serializeDoctor($doctor), $doctors),
            'appointments' => array_map(fn(Appointment $appointment) => $this->serializeAppointment($appointment), $appointments),
            'users' => array_map(fn(User $user) => $this->serializeUser($user), $users),
        ]);
    }

    #[Route('/clinics', name: 'api_admin_clinics_index', methods: ['GET'])]
    public function clinics(ClinicRepository $clinicRepository): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->json(array_map(
            fn(Clinic $clinic) => $this->serializeClinic($clinic),
            $clinicRepository->findBy([], ['id' => 'DESC'])
        ));
    }

    #[Route('/clinics', name: 'api_admin_clinics_create', methods: ['POST'])]
    public function createClinic(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $data = json_decode($request->getContent(), true) ?: [];
        $name = trim((string) ($data['name'] ?? ''));
        $address = trim((string) ($data['address'] ?? ''));
        $phone = trim((string) ($data['phone'] ?? ''));

        if ($name === '' || $address === '' || $phone === '') {
            return $this->json(['error' => 'Donnees clinique invalides'], 400);
        }

        $clinic = new Clinic();
        $clinic->setName($name);
        $clinic->setAddress($address);
        $clinic->setPhone($phone);

        $entityManager->persist($clinic);
        $entityManager->flush();

        return $this->json([
            'message' => 'Clinique creee',
            'clinic' => $this->serializeClinic($clinic),
        ], 201);
    }

    #[Route('/clinics/{id}', name: 'api_admin_clinics_update', methods: ['PUT'])]
    public function updateClinic(Clinic $clinic, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $data = json_decode($request->getContent(), true) ?: [];
        $name = trim((string) ($data['name'] ?? ''));
        $address = trim((string) ($data['address'] ?? ''));
        $phone = trim((string) ($data['phone'] ?? ''));

        if ($name === '' || $address === '' || $phone === '') {
            return $this->json(['error' => 'Donnees clinique invalides'], 400);
        }

        $clinic->setName($name);
        $clinic->setAddress($address);
        $clinic->setPhone($phone);

        $entityManager->flush();

        return $this->json([
            'message' => 'Clinique mise a jour',
            'clinic' => $this->serializeClinic($clinic),
        ]);
    }

    #[Route('/clinics/{id}', name: 'api_admin_clinics_delete', methods: ['DELETE'])]
    public function deleteClinic(Clinic $clinic, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $entityManager->remove($clinic);
        $entityManager->flush();

        return $this->json(['message' => 'Clinique supprimee']);
    }

    #[Route('/doctors', name: 'api_admin_doctors_index', methods: ['GET'])]
    public function doctors(DoctorRepository $doctorRepository): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->json(array_map(
            fn(Doctor $doctor) => $this->serializeDoctor($doctor),
            $doctorRepository->findBy([], ['id' => 'DESC'])
        ));
    }

    #[Route('/doctors', name: 'api_admin_doctors_create', methods: ['POST'])]
    public function createDoctor(
        Request $request,
        EntityManagerInterface $entityManager,
        ClinicRepository $clinicRepository
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $data = json_decode($request->getContent(), true) ?: [];
        $clinic = $clinicRepository->find((int) ($data['clinic_id'] ?? 0));
        $fullName = trim((string) ($data['fullName'] ?? ''));
        $specialty = trim((string) ($data['specialty'] ?? ''));
        $phone = trim((string) ($data['phone'] ?? ''));

        if (!$clinic instanceof Clinic || $fullName === '' || $specialty === '' || $phone === '') {
            return $this->json(['error' => 'Donnees medecin invalides'], 400);
        }

        $doctor = new Doctor();
        $doctor->setClinic($clinic);
        $doctor->setFullName($fullName);
        $doctor->setSpecialty($specialty);
        $doctor->setPhone($phone);

        $entityManager->persist($doctor);
        $entityManager->flush();

        return $this->json([
            'message' => 'Medecin cree',
            'doctor' => $this->serializeDoctor($doctor),
        ], 201);
    }

    #[Route('/doctors/{id}', name: 'api_admin_doctors_update', methods: ['PUT'])]
    public function updateDoctor(
        Doctor $doctor,
        Request $request,
        EntityManagerInterface $entityManager,
        ClinicRepository $clinicRepository
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $data = json_decode($request->getContent(), true) ?: [];
        $clinic = $clinicRepository->find((int) ($data['clinic_id'] ?? 0));
        $fullName = trim((string) ($data['fullName'] ?? ''));
        $specialty = trim((string) ($data['specialty'] ?? ''));
        $phone = trim((string) ($data['phone'] ?? ''));

        if (!$clinic instanceof Clinic || $fullName === '' || $specialty === '' || $phone === '') {
            return $this->json(['error' => 'Donnees medecin invalides'], 400);
        }

        $doctor->setClinic($clinic);
        $doctor->setFullName($fullName);
        $doctor->setSpecialty($specialty);
        $doctor->setPhone($phone);

        $entityManager->flush();

        return $this->json([
            'message' => 'Medecin mis a jour',
            'doctor' => $this->serializeDoctor($doctor),
        ]);
    }

    #[Route('/doctors/{id}', name: 'api_admin_doctors_delete', methods: ['DELETE'])]
    public function deleteDoctor(Doctor $doctor, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $entityManager->remove($doctor);
        $entityManager->flush();

        return $this->json(['message' => 'Medecin supprime']);
    }

    #[Route('/availabilities', name: 'api_admin_availabilities_create', methods: ['POST'])]
    public function createAvailability(
        Request $request,
        EntityManagerInterface $entityManager,
        DoctorRepository $doctorRepository
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $data = json_decode($request->getContent(), true) ?: [];
        $doctor = $doctorRepository->find((int) ($data['doctor_id'] ?? 0));
        $weekday = (int) ($data['weekday'] ?? 0);
        $startTimeRaw = trim((string) ($data['startTime'] ?? ''));
        $endTimeRaw = trim((string) ($data['endTime'] ?? ''));

        if (!$doctor instanceof Doctor || $weekday < 1 || $weekday > 7) {
            return $this->json(['error' => 'Donnees disponibilite invalides'], 400);
        }

        $startTime = \DateTime::createFromFormat('H:i', $startTimeRaw);
        $endTime = \DateTime::createFromFormat('H:i', $endTimeRaw);

        if (!$startTime instanceof \DateTime || !$endTime instanceof \DateTime || $startTime >= $endTime) {
            return $this->json(['error' => 'Plage horaire invalide'], 400);
        }

        $availability = new DoctorAvailability();
        $availability->setDoctor($doctor);
        $availability->setWeekday($weekday);
        $availability->setStartTime($startTime);
        $availability->setEndTime($endTime);

        $entityManager->persist($availability);
        $entityManager->flush();

        return $this->json([
            'message' => 'Disponibilite ajoutee',
            'availability' => $this->serializeAvailability($availability),
        ], 201);
    }

    #[Route('/availabilities/{id}', name: 'api_admin_availabilities_delete', methods: ['DELETE'])]
    public function deleteAvailability(DoctorAvailability $availability, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $entityManager->remove($availability);
        $entityManager->flush();

        return $this->json(['message' => 'Disponibilite supprimee']);
    }

    #[Route('/appointments', name: 'api_admin_appointments_index', methods: ['GET'])]
    public function appointments(AppointmentRepository $appointmentRepository): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->json(array_map(
            fn(Appointment $appointment) => $this->serializeAppointment($appointment),
            $appointmentRepository->findBy([], ['appointmentAt' => 'DESC'])
        ));
    }

    #[Route('/appointments/{id}/status', name: 'api_admin_appointments_status', methods: ['PUT'])]
    public function updateAppointmentStatus(
        Appointment $appointment,
        Request $request,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $data = json_decode($request->getContent(), true) ?: [];
        $status = (string) ($data['status'] ?? '');

        if (!in_array($status, [Appointment::STATUS_PENDING, Appointment::STATUS_CONFIRMED, Appointment::STATUS_CANCELLED], true)) {
            return $this->json(['error' => 'Statut invalide'], 400);
        }

        $appointment->setStatus($status);
        $entityManager->flush();

        return $this->json([
            'message' => 'Statut mis a jour',
            'appointment' => $this->serializeAppointment($appointment),
        ]);
    }

    #[Route('/users', name: 'api_admin_users_index', methods: ['GET'])]
    public function users(UserRepository $userRepository): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->json(array_map(
            fn(User $user) => $this->serializeUser($user),
            $userRepository->findBy([], ['id' => 'DESC'])
        ));
    }

    private function serializeClinic(Clinic $clinic): array
    {
        return [
            'id' => $clinic->getId(),
            'name' => $clinic->getName(),
            'address' => $clinic->getAddress(),
            'phone' => $clinic->getPhone(),
            'doctorCount' => $clinic->getDoctors()->count(),
        ];
    }

    private function serializeDoctor(Doctor $doctor): array
    {
        return [
            'id' => $doctor->getId(),
            'fullName' => $doctor->getFullName(),
            'specialty' => $doctor->getSpecialty(),
            'phone' => $doctor->getPhone(),
            'clinic' => [
                'id' => $doctor->getClinic()?->getId(),
                'name' => $doctor->getClinic()?->getName(),
            ],
            'availabilities' => array_map(
                fn(DoctorAvailability $availability) => $this->serializeAvailability($availability),
                $doctor->getAvailabilities()->toArray()
            ),
        ];
    }

    private function serializeAvailability(DoctorAvailability $availability): array
    {
        return [
            'id' => $availability->getId(),
            'doctorId' => $availability->getDoctor()?->getId(),
            'weekday' => $availability->getWeekday(),
            'startTime' => $availability->getStartTime()?->format('H:i'),
            'endTime' => $availability->getEndTime()?->format('H:i'),
        ];
    }

    private function serializeAppointment(Appointment $appointment): array
    {
        return [
            'id' => $appointment->getId(),
            'date' => $appointment->getAppointmentAt()?->format('Y-m-d'),
            'time' => $appointment->getAppointmentAt()?->format('H:i'),
            'duration' => $appointment->getDurationMinutes(),
            'status' => $appointment->getStatus(),
            'note' => $appointment->getNote(),
            'patient' => [
                'id' => $appointment->getPatient()?->getId(),
                'firstname' => $appointment->getPatient()?->getFirstname(),
                'name' => $appointment->getPatient()?->getName(),
                'email' => $appointment->getPatient()?->getEmail(),
            ],
            'doctor' => [
                'id' => $appointment->getDoctor()?->getId(),
                'fullName' => $appointment->getDoctor()?->getFullName(),
                'specialty' => $appointment->getDoctor()?->getSpecialty(),
                'clinic' => $appointment->getDoctor()?->getClinic()?->getName(),
            ],
        ];
    }

    private function serializeUser(User $user): array
    {
        return [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'firstname' => $user->getFirstname(),
            'name' => $user->getName(),
            'phone' => $user->getPhone(),
            'dateOfBirth' => $user->getDateOfBirth()?->format('Y-m-d'),
            'roles' => $user->getRoles(),
        ];
    }
}
