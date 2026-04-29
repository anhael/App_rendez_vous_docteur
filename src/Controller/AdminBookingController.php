<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Entity\Property;
use App\Entity\User;
use App\Repository\BookingRepository;
use App\Repository\PropertyRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/bookings')]
class AdminBookingController extends AbstractController
{
    #[Route('', name: 'admin_bookings_index', methods: ['GET'])]
    public function index(BookingRepository $bookingRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('admin/booking/index.html.twig', [
            'bookings' => $bookingRepository->findBy([], ['id' => 'DESC']),
        ]);
    }

    #[Route('/new', name: 'admin_bookings_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        PropertyRepository $propertyRepository,
        UserRepository $userRepository
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($request->isMethod('POST')) {
            $propertyId = (int) $request->request->get('property_id', 0);
            $userId = (int) $request->request->get('user_id', 0);
            $startDateRaw = (string) $request->request->get('startDate', '');
            $endDateRaw = (string) $request->request->get('endDate', '');

            $property = $propertyRepository->find($propertyId);
            $user = $userRepository->find($userId);

            if ($property instanceof Property && $user instanceof User && $startDateRaw && $endDateRaw) {
                $booking = new Booking();
                $booking->setProperty($property);
                $booking->setUser($user);
                $booking->setStartDate(new \DateTime($startDateRaw));
                $booking->setEndDate(new \DateTime($endDateRaw));

                $entityManager->persist($booking);
                $entityManager->flush();

                $this->addFlash('success', 'Réservation créée.');

                return $this->redirectToRoute('admin_bookings_index');
            }

            $this->addFlash('danger', 'Veuillez sélectionner un logement, un client et des dates valides.');
        }

        return $this->render('admin/booking/form.html.twig', [
            'properties' => $propertyRepository->findBy([], ['title' => 'ASC']),
            'users' => $userRepository->findBy([], ['email' => 'ASC']),
            'booking' => null,
            'mode' => 'new',
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_bookings_edit', methods: ['GET', 'POST'])]
    public function edit(
        Booking $booking,
        Request $request,
        EntityManagerInterface $entityManager,
        PropertyRepository $propertyRepository,
        UserRepository $userRepository
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($request->isMethod('POST')) {
            $propertyId = (int) $request->request->get('property_id', 0);
            $userId = (int) $request->request->get('user_id', 0);
            $startDateRaw = (string) $request->request->get('startDate', '');
            $endDateRaw = (string) $request->request->get('endDate', '');

            $property = $propertyRepository->find($propertyId);
            $user = $userRepository->find($userId);

            if ($property instanceof Property && $user instanceof User && $startDateRaw && $endDateRaw) {
                $startDate = new \DateTime($startDateRaw);
                $endDate = new \DateTime($endDateRaw);

                $conflict = false;
                foreach ($property->getBookings() as $existingBooking) {
                    if ($existingBooking->getId() === $booking->getId()) {
                        continue;
                    }

                    if (($startDate < $existingBooking->getEndDate()) && ($endDate > $existingBooking->getStartDate())) {
                        $conflict = true;
                        break;
                    }
                }

                if (!$conflict) {
                    $booking->setProperty($property);
                    $booking->setUser($user);
                    $booking->setStartDate($startDate);
                    $booking->setEndDate($endDate);

                    $entityManager->flush();

                    $this->addFlash('success', 'Réservation mise à jour.');

                    return $this->redirectToRoute('admin_bookings_index');
                }

                $this->addFlash('danger', 'Conflit détecté sur les dates choisies.');
            } else {
                $this->addFlash('danger', 'Veuillez sélectionner un logement, un client et des dates valides.');
            }
        }

        return $this->render('admin/booking/form.html.twig', [
            'properties' => $propertyRepository->findBy([], ['title' => 'ASC']),
            'users' => $userRepository->findBy([], ['email' => 'ASC']),
            'booking' => $booking,
            'mode' => 'edit',
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_bookings_delete', methods: ['POST'])]
    public function delete(Booking $booking, Request $request, EntityManagerInterface $entityManager): RedirectResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->isCsrfTokenValid('delete_booking_' . $booking->getId(), (string) $request->request->get('_token'))) {
            $entityManager->remove($booking);
            $entityManager->flush();
            $this->addFlash('success', 'Réservation supprimée.');
        }

        return $this->redirectToRoute('admin_bookings_index');
    }
}
