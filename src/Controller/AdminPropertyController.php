<?php

namespace App\Controller;

use App\Entity\Property;
use App\Repository\PropertyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/properties')]
class AdminPropertyController extends AbstractController
{
    #[Route('', name: 'admin_properties_index', methods: ['GET'])]
    public function index(PropertyRepository $propertyRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('admin/property/index.html.twig', [
            'properties' => $propertyRepository->findBy([], ['id' => 'DESC']),
        ]);
    }

    #[Route('/new', name: 'admin_properties_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($request->isMethod('POST')) {
            $title = trim((string) $request->request->get('title', ''));
            $description = trim((string) $request->request->get('description', ''));
            $price = (float) $request->request->get('price', 0);

            if ($title !== '' && $price > 0) {
                $property = new Property();
                $property->setTitle($title);
                $property->setDescription($description ?: null);
                $property->setPrice($price);
                $property->setCreatedAt(new \DateTimeImmutable());

                $entityManager->persist($property);
                $entityManager->flush();

                $this->addFlash('success', 'Logement créé.');

                return $this->redirectToRoute('admin_properties_index');
            }

            $this->addFlash('danger', 'Veuillez renseigner un titre et un prix valide.');
        }

        return $this->render('admin/property/form.html.twig', [
            'property' => null,
            'mode' => 'new',
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_properties_edit', methods: ['GET', 'POST'])]
    public function edit(Property $property, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($request->isMethod('POST')) {
            $title = trim((string) $request->request->get('title', ''));
            $description = trim((string) $request->request->get('description', ''));
            $price = (float) $request->request->get('price', 0);

            if ($title !== '' && $price > 0) {
                $property->setTitle($title);
                $property->setDescription($description ?: null);
                $property->setPrice($price);

                $entityManager->flush();

                $this->addFlash('success', 'Logement mis à jour.');

                return $this->redirectToRoute('admin_properties_index');
            }

            $this->addFlash('danger', 'Veuillez renseigner un titre et un prix valide.');
        }

        return $this->render('admin/property/form.html.twig', [
            'property' => $property,
            'mode' => 'edit',
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_properties_delete', methods: ['POST'])]
    public function delete(Property $property, Request $request, EntityManagerInterface $entityManager): RedirectResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->isCsrfTokenValid('delete_property_' . $property->getId(), (string) $request->request->get('_token'))) {
            $entityManager->remove($property);
            $entityManager->flush();
            $this->addFlash('success', 'Logement supprimé.');
        }

        return $this->redirectToRoute('admin_properties_index');
    }
}
