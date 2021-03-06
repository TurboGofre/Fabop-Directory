<?php

namespace App\Controller;

use App\Entity\Log;
use App\Repository\LogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/log")
 */
class LogController extends AbstractController
{
    /**
     * @Route("/", name="log_index", methods={"GET"})
     */
    public function index(LogRepository $logRepository): Response
    {
        return $this->render('log/index.html.twig', [
            'logs' => $logRepository->findAll(),
        ]);
    }


    /**
     * @Route("/{id}", name="log_show", methods={"GET"})
     */
    public function show(Log $log): Response
    {
	if($this->isGranted('POST_VIEW',$log)){
		return $this->render('log/show.html.twig', [
		    'log' => $log,
		]);
	}
	else{
		return $this->render('error403forbidden.html.twig');
	}
	return $this->redirectToRoute('log_index');
    }

    /**
     * @Route("/{id}", name="log_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Log $log): Response
    {
	if($this->isGranted('POST_VIEW',$log)){
		if ($this->isCsrfTokenValid('delete'.$log->getId(), $request->request->get('_token'))) {
		    $entityManager = $this->getDoctrine()->getManager();
		    $entityManager->remove($log);
		    $entityManager->flush();
		}
	}
	else{
		return $this->render('error403forbidden.html.twig');
	}
        return $this->redirectToRoute('log_index');
    }
}
