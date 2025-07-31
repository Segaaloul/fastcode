<?php

namespace App\Controller;

use App\Entity\Download;
use App\Form\DownloadType;
use App\Repository\DownloadRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/download')]
final class DownloadController extends AbstractController
{
    #[Route(name: 'app_download_index', methods: ['GET'])]
    public function index(DownloadRepository $downloadRepository): Response
    {
        return $this->render('download/index.html.twig', [
            'downloads' => $downloadRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_download_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $download = new Download();
        $form = $this->createForm(DownloadType::class, $download);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($download);
            $entityManager->flush();

            return $this->redirectToRoute('app_download_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('download/new.html.twig', [
            'download' => $download,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_download_show', methods: ['GET'])]
    public function show(Download $download): Response
    {
        return $this->render('download/show.html.twig', [
            'download' => $download,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_download_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Download $download, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(DownloadType::class, $download);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_download_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('download/edit.html.twig', [
            'download' => $download,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_download_delete', methods: ['POST'])]
    public function delete(Request $request, Download $download, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$download->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($download);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_download_index', [], Response::HTTP_SEE_OTHER);
    }
}
