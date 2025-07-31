<?php

namespace App\Controller;

use App\Entity\Solution;
use App\Form\SolutionType;
use App\Repository\SolutionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use App\Entity\Download;
use Symfony\Bundle\SecurityBundle\Security;


#[Route('/solution')]
final class SolutionController extends AbstractController
{
    #[Route(name: 'app_solution_index', methods: ['GET'])]
    public function index(SolutionRepository $solutionRepository): Response
    {
        return $this->render('solution/index.html.twig', [
            'solutions' => $solutionRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_solution_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $solution = new Solution();
        $form = $this->createForm(SolutionType::class, $solution);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            $imageFile = $form->get('imagePath')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $newFilename = $originalFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('kernel.project_dir') . '/uploads/solutions/images',
                        $newFilename
                    );
                } catch (FileException $e) {
                    throw new \Exception('Erreur d’upload image : ' . $e->getMessage());
                }

                $solution->setImagePath('uploads/solutions/images/' . $newFilename);
            }

            $zipFile = $form->get('zipFilePath')->getData();

            if ($zipFile) {
                $originalZipName = pathinfo($zipFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeZipName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $originalZipName);
                $newZipFilename = $safeZipName . '-' . uniqid() . '.' . $zipFile->guessExtension();

                $zipDestination = $this->getParameter('kernel.project_dir') . '/uploads/solutions/zipfile';

                if (!file_exists($zipDestination)) {
                    mkdir($zipDestination, 0777, true);
                }

                try {
                    $zipFile->move($zipDestination, $newZipFilename);
                    $solution->setZipFilePath('uploads/solutions/zipfile/' . $newZipFilename);
                } catch (FileException $e) {
                    throw new \Exception('Erreur lors de l’upload du fichier ZIP');
                }
            }


            $solution->setDateAjout(new \DateTimeImmutable());

            $entityManager->persist($solution);
            $entityManager->flush();

            return $this->redirectToRoute('app_solution_index', [], Response::HTTP_SEE_OTHER);
        }


        return $this->render('solution/new.html.twig', [
            'solution' => $solution,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_solution_show', methods: ['GET'])]
    public function show(Solution $solution): Response
    {
        return $this->render('solution/show.html.twig', [
            'solution' => $solution,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_solution_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Solution $solution, EntityManagerInterface $entityManager): Response
    {
        // Sauvegarder les anciens chemins au cas où on ne change pas les fichiers
        $oldImagePath = $solution->getImagePath();
        $oldZipPath = $solution->getZipFilePath();

        $form = $this->createForm(SolutionType::class, $solution);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // 🖼️ Gestion de l'image
            $imageFile = $form->get('imagePath')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $newFilename = $originalFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('kernel.project_dir') . '/uploads/solutions/images',
                        $newFilename
                    );
                    $solution->setImagePath('uploads/solutions/images/' . $newFilename);
                } catch (FileException $e) {
                    throw new \Exception('Erreur d’upload image : ' . $e->getMessage());
                }
            } else {
                $solution->setImagePath($oldImagePath);
            }

            // 🗜️ Gestion du fichier ZIP
            $zipFile = $form->get('zipFilePath')->getData();
            if ($zipFile) {
                $originalZipName = pathinfo($zipFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeZipName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $originalZipName);
                $newZipFilename = $safeZipName . '-' . uniqid() . '.' . $zipFile->guessExtension();

                $zipDestination = $this->getParameter('kernel.project_dir') . '/uploads/solutions/zipfile';

                if (!file_exists($zipDestination)) {
                    mkdir($zipDestination, 0777, true);
                }

                try {
                    $zipFile->move($zipDestination, $newZipFilename);
                    $solution->setZipFilePath('uploads/solutions/zipfile/' . $newZipFilename);
                } catch (FileException $e) {
                    throw new \Exception('Erreur d’upload ZIP : ' . $e->getMessage());
                }
            } else {
                $solution->setZipFilePath($oldZipPath);
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_solution_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('solution/edit.html.twig', [
            'solution' => $solution,
            'form' => $form,
        ]);
    }


    #[Route('/{id}', name: 'app_solution_delete', methods: ['POST'])]
    public function delete(Request $request, Solution $solution, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $solution->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($solution);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_solution_index', [], Response::HTTP_SEE_OTHER);
    }


    // cette route permet dafficher les images, car elle ne sont pas dans le dossier public
    #[Route('/solution/image/{filename}', name: 'solution_image')]
    public function image(string $filename): Response
    {
        $path = $this->getParameter('kernel.project_dir') . '/uploads/solutions/images/' . $filename;

        if (!file_exists($path)) {
            throw $this->createNotFoundException('Image non trouvée');
        }

        return new BinaryFileResponse($path);
    }


    #[Route('/solution/zip/{id}', name: 'solution_zip')]
    public function zip(Solution $solution, EntityManagerInterface $entityManager, Security $security): BinaryFileResponse
    {
        $zipPath = $this->getParameter('kernel.project_dir') . '/' . $solution->getZipFilePath();

        if (!file_exists($zipPath)) {
            throw $this->createNotFoundException('Fichier introuvable');
        }


        // ✅ Enregistrer le téléchargement
        $download = new Download();
        $download->setUser($security->getUser());
        $download->setSolution($solution);
        $download->setDate(new \DateTimeImmutable());

        $entityManager->persist($download);
        $entityManager->flush();


        // Extraire le nom réel du fichier
        $filename = basename($zipPath);

        $response = new BinaryFileResponse($zipPath);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $filename
        );

        return $response;
    }
}
