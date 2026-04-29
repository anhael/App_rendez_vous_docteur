<?php

namespace App\Controller\Api;

use App\Entity\Appointment;
use App\Entity\Doctor;
use App\Entity\User;
use App\Repository\AppointmentRepository;
use App\Repository\DoctorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class BookingController extends AbstractController
{
    #[Route('/api/appointments', name: 'api_appointments_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        DoctorRepository $doctorRepository,
        AppointmentRepository $appointmentRepository,
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['error' => 'Acces refuse'], 401);
        }

        $data = json_decode($request->getContent(), true) ?: [];
        $doctor = $doctorRepository->find((int) ($data['doctor_id'] ?? 0));
        $date = trim((string) ($data['date'] ?? ''));
        $time = trim((string) ($data['time'] ?? ''));
        $duration = max(15, (int) ($data['duration'] ?? 30));
        $note = trim((string) ($data['note'] ?? ''));

        if (!$doctor instanceof Doctor || $date === '' || $time === '') {
            return $this->json(['error' => 'Donnees invalides'], 400);
        }

        $appointmentAt = \DateTime::createFromFormat('Y-m-d H:i', $date . ' ' . $time);
        if (!$appointmentAt instanceof \DateTime) {
            return $this->json(['error' => 'Format date/heure invalide'], 400);
        }

        if ($appointmentAt < new \DateTime('-1 minute')) {
            return $this->json(['error' => 'Vous ne pouvez pas reserver dans le passe'], 400);
        }

        if (!$this->isDoctorAvailable($doctor, $appointmentAt, $duration)) {
            return $this->json(['error' => 'Ce creneau est hors disponibilite du medecin'], 409);
        }

        if ($this->hasConflict($appointmentRepository, $doctor, $appointmentAt, $duration, null)) {
            return $this->json(['error' => 'Ce creneau est deja reserve'], 409);
        }

        $appointment = new Appointment();
        $appointment->setPatient($user);
        $appointment->setDoctor($doctor);
        $appointment->setAppointmentAt($appointmentAt);
        $appointment->setDurationMinutes($duration);
        $appointment->setStatus(Appointment::STATUS_PENDING);
        $appointment->setNote($note !== '' ? $note : null);
        $appointment->setCreatedAt(new \DateTimeImmutable());

        $em->persist($appointment);
        $em->flush();

        return $this->json([
            'message' => 'Rendez-vous enregistre',
            'id' => $appointment->getId(),
        ]);
    }

    #[Route('/api/appointments/me', name: 'api_appointments_me', methods: ['GET'])]
    public function myBookings(
        AppointmentRepository $appointmentRepository
    ): JsonResponse {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->json(['error' => 'Acces refuse'], 401);
        }

        $appointments = $appointmentRepository->findBy(['patient' => $user], ['appointmentAt' => 'ASC']);

        return $this->json(array_map(static fn(Appointment $appointment) => [
            'id' => $appointment->getId(),
            'doctor' => $appointment->getDoctor()?->getFullName(),
            'specialty' => $appointment->getDoctor()?->getSpecialty(),
            'clinic' => $appointment->getDoctor()?->getClinic()?->getName(),
            'date' => $appointment->getAppointmentAt()?->format('Y-m-d'),
            'time' => $appointment->getAppointmentAt()?->format('H:i'),
            'duration' => $appointment->getDurationMinutes(),
            'status' => $appointment->getStatus(),
            'note' => $appointment->getNote(),
        ], $appointments));
    }

    #[Route('/api/appointments/calendar', name: 'api_appointments_calendar', methods: ['GET'])]
    public function myCalendar(Request $request, AppointmentRepository $appointmentRepository): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->json(['error' => 'Acces refuse'], 401);
        }

        $month = trim((string) $request->query->get('month', ''));
        $start = \DateTime::createFromFormat('Y-m-d H:i:s', ($month !== '' ? $month : date('Y-m')) . '-01 00:00:00');

        if (!$start instanceof \DateTime) {
            $start = new \DateTime(date('Y-m-01 00:00:00'));
        }

        $end = (clone $start)->modify('first day of next month');

        $appointments = $appointmentRepository->createQueryBuilder('a')
            ->andWhere('a.patient = :patient')
            ->andWhere('a.appointmentAt >= :start')
            ->andWhere('a.appointmentAt < :end')
            ->setParameter('patient', $user)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('a.appointmentAt', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->json(array_map(static fn(Appointment $appointment) => [
            'id' => $appointment->getId(),
            'date' => $appointment->getAppointmentAt()?->format('Y-m-d'),
            'time' => $appointment->getAppointmentAt()?->format('H:i'),
            'doctor' => $appointment->getDoctor()?->getFullName(),
            'status' => $appointment->getStatus(),
        ], $appointments));
    }

    private function isDoctorAvailable(Doctor $doctor, \DateTimeInterface $appointmentAt, int $duration): bool
    {
        $weekday = (int) $appointmentAt->format('N');
        $requestedStart = $appointmentAt->format('H:i');
        $requestedEnd = $this->addMinutes($appointmentAt, $duration)->format('H:i');

        foreach ($doctor->getAvailabilities() as $availability) {
            if ($availability->getWeekday() !== $weekday) {
                continue;
            }

            $start = $availability->getStartTime()?->format('H:i');
            $end = $availability->getEndTime()?->format('H:i');

            if ($start !== null && $end !== null && $requestedStart >= $start && $requestedEnd <= $end) {
                return true;
            }
        }

        return false;
    }

    private function hasConflict(
        AppointmentRepository $appointmentRepository,
        Doctor $doctor,
        \DateTimeInterface $appointmentAt,
        int $duration,
        ?int $excludeId
    ): bool {
        $dayStart = new \DateTime($appointmentAt->format('Y-m-d 00:00:00'));
        $dayEnd = (clone $dayStart)->modify('+1 day');
        $requestedEnd = $this->addMinutes($appointmentAt, $duration);

        $appointments = $appointmentRepository->createQueryBuilder('a')
            ->andWhere('a.doctor = :doctor')
            ->andWhere('a.status != :cancelled')
            ->andWhere('a.appointmentAt >= :dayStart')
            ->andWhere('a.appointmentAt < :dayEnd')
            ->setParameter('doctor', $doctor)
            ->setParameter('cancelled', Appointment::STATUS_CANCELLED)
            ->setParameter('dayStart', $dayStart)
            ->setParameter('dayEnd', $dayEnd)
            ->orderBy('a.appointmentAt', 'ASC')
            ->getQuery()
            ->getResult();

        foreach ($appointments as $existing) {
            if (!$existing instanceof Appointment) {
                continue;
            }

            if ($excludeId !== null && $existing->getId() === $excludeId) {
                continue;
            }

            $existingStart = $existing->getAppointmentAt();
            if (!$existingStart instanceof \DateTimeInterface) {
                continue;
            }

            $existingEnd = $this->addMinutes($existingStart, $existing->getDurationMinutes());

            if ($appointmentAt < $existingEnd && $requestedEnd > $existingStart) {
                return true;
            }
        }

        return false;
    }

    private function addMinutes(\DateTimeInterface $dateTime, int $minutes): \DateTimeImmutable
    {
        $immutable = new \DateTimeImmutable($dateTime->format('Y-m-d H:i:s'));

        return $immutable->modify('+' . $minutes . ' minutes');
    }
}
