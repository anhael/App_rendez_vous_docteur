<?php

namespace App\Command;

use App\Entity\Appointment;
use App\Entity\Clinic;
use App\Entity\Doctor;
use App\Entity\DoctorAvailability;
use App\Entity\User;
use App\Repository\ClinicRepository;
use App\Repository\DoctorRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:seed-demo-data',
    description: 'Seed demo data for medical appointment app',
)]
class SeedDemoDataCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
        private readonly ClinicRepository $clinicRepository,
        private readonly DoctorRepository $doctorRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $patient = $this->userRepository->findOneBy(['email' => 'patient@medicline.local']);
        if (!$patient instanceof User) {
            $patient = new User();
            $patient->setEmail('patient@medicline.local');
            $this->entityManager->persist($patient);
        }

        $patient->setFirstname('Nadia');
        $patient->setName('Benali');
        $patient->setPhone('0612345678');
        $patient->setRoles(['ROLE_USER']);
        $patient->setDateOfBirth(new \DateTime('1993-05-14'));
        $patient->setPassword($this->passwordHasher->hashPassword($patient, 'Patient123!'));

        $admin = $this->userRepository->findOneBy(['email' => 'admin@medicline.local']);
        if (!$admin instanceof User) {
            $admin = new User();
            $admin->setEmail('admin@medicline.local');
            $this->entityManager->persist($admin);
        }

        $admin->setFirstname('Admin');
        $admin->setName('Clinic');
        $admin->setPhone('0600000000');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'Admin123!'));

        $clinicData = [
            ['Clinique Sainte-Marie', '12 avenue de la Sante, Casablanca', '0522001122'],
            ['Polyclinique Atlas', '7 boulevard Mohammed V, Rabat', '0537002233'],
            ['Centre Medical Horizon', '88 route des Palmiers, Marrakech', '0524303344'],
        ];

        $createdClinics = 0;
        foreach ($clinicData as [$name, $address, $phone]) {
            $clinic = $this->clinicRepository->findOneBy(['name' => $name]);
            if (!$clinic instanceof Clinic) {
                $clinic = new Clinic();
                $clinic->setName($name);
                $createdClinics++;
                $this->entityManager->persist($clinic);
            }

            $clinic->setAddress($address);
            $clinic->setPhone($phone);
        }

        $this->entityManager->flush();

        $doctorData = [
            ['Dr. Amine El Idrissi', 'Cardiologie', '0600112233', 'Clinique Sainte-Marie'],
            ['Dr. Salma Ait Lahcen', 'Dermatologie', '0600223344', 'Polyclinique Atlas'],
            ['Dr. Mehdi Bakkali', 'Pediatrie', '0600334455', 'Centre Medical Horizon'],
        ];

        $createdDoctors = 0;
        $createdAvailabilities = 0;

        foreach ($doctorData as [$fullName, $specialty, $phone, $clinicName]) {
            $clinic = $this->clinicRepository->findOneBy(['name' => $clinicName]);
            if (!$clinic instanceof Clinic) {
                continue;
            }

            $doctor = $this->doctorRepository->findOneBy(['fullName' => $fullName]);
            if (!$doctor instanceof Doctor) {
                $doctor = new Doctor();
                $doctor->setFullName($fullName);
                $this->entityManager->persist($doctor);
                $createdDoctors++;
            }

            $doctor->setSpecialty($specialty);
            $doctor->setPhone($phone);
            $doctor->setClinic($clinic);

            if ($doctor->getAvailabilities()->count() === 0) {
                $slots = [
                    [1, '09:00', '12:00'],
                    [3, '14:00', '18:00'],
                    [5, '09:00', '13:00'],
                ];

                foreach ($slots as [$weekday, $start, $end]) {
                    $availability = new DoctorAvailability();
                    $availability->setDoctor($doctor);
                    $availability->setWeekday($weekday);
                    $availability->setStartTime(\DateTime::createFromFormat('H:i', $start));
                    $availability->setEndTime(\DateTime::createFromFormat('H:i', $end));
                    $this->entityManager->persist($availability);
                    $createdAvailabilities++;
                }
            }
        }

        $this->entityManager->flush();

        $createdAppointments = 0;
        $firstDoctor = $this->doctorRepository->findOneBy([], ['id' => 'ASC']);
        if ($patient instanceof User && $firstDoctor instanceof Doctor) {
            $existingAppointment = $this->entityManager->getRepository(Appointment::class)->findOneBy([
                'patient' => $patient,
                'doctor' => $firstDoctor,
            ]);

            if (!$existingAppointment instanceof Appointment) {
                $appointment = new Appointment();
                $appointment->setPatient($patient);
                $appointment->setDoctor($firstDoctor);
                $appointment->setAppointmentAt(new \DateTime('+2 days 09:30'));
                $appointment->setDurationMinutes(30);
                $appointment->setStatus(Appointment::STATUS_CONFIRMED);
                $appointment->setNote('Controle de suivi');
                $appointment->setCreatedAt(new \DateTimeImmutable());

                $this->entityManager->persist($appointment);
                $createdAppointments++;
            }
        }

        $this->entityManager->flush();

        $io->success(sprintf(
            'Seed termine: %d clinique(s), %d medecin(s), %d disponibilite(s), %d rendez-vous.',
            $createdClinics,
            $createdDoctors,
            $createdAvailabilities,
            $createdAppointments
        ));

        $io->note('Admin: admin@medicline.local / Admin123! | Patient: patient@medicline.local / Patient123!');

        return Command::SUCCESS;
    }
}
