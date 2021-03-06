<?php

namespace App\Controller;

use App\Entity\EntityInstitutions;
use App\Form\EntityInstitutionsType;
use App\Repository\EntityInstitutionsRepository;
use App\Security\Voter\PermissionCalculator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Utils\MongoManager;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/manager/institutions")
 */
class EntityInstitutionsController extends AbstractController
{
    /**
     * @Route("/", name="entity_institutions_index", methods="GET")
     * @param EntityInstitutionsRepository $entityInstitutionsRepository
     * @return Response
     */
    public function index(EntityInstitutionsRepository $entityInstitutionsRepository): Response
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        //filtres à appliquer ici
        $list = PermissionCalculator::checkRight($user,"institutions",$entityInstitutionsRepository->findAll(),"read");
        $edit = PermissionCalculator::checkRight($user,"institutions",$list,"write");
        return $this->render('entity_institutions/index.html.twig', ['entity_institutions' => $list, 'edits' => $edit]);
    }

    /**
     * @Route("/new", name="entity_institutions_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $entityInstitution = new EntityInstitutions();
        if($this->isGranted('POST_EDIT',$entityInstitution)){
            $form = $this->createForm(EntityInstitutionsType::class, $entityInstitution);
            $form->handleRequest($request);
            $mongoman = new MongoManager();

            if ($form->isSubmitted() && $form->isValid()) {
                $em = $this->getDoctrine()->getManager();

                // Mise en bdd Mongo de l fiche doc --> return IdMongo
                if (null != $request->request->get('institution_data')){
                    $sheetId=$mongoman->insertSingle("Entity_institution_sheet",$request->request->get('institution_data'));
                }else{
                    $sheetId=$mongoman->insertSingle("Entity_institution_sheet",[]);
                }

                // Mise en bdd MySQL de l'ID de fiche de données
                $entityInstitution->setSheetId($sheetId);

                $em->persist($entityInstitution);
                $em->flush();

                return $this->redirectToRoute('entity_institutions_index');
            }

            return $this->render('entity_institutions/new.html.twig', [
                'entity_institution' => $entityInstitution,
                'form' => $form->createView(),
            ]);
        }
        else{
            return $this->render('error403forbidden.html.twig');
        }
	    return $this->redirectToRoute('entity_institutions_index');
    }

    /**
     * @Route("/{id}", name="entity_institutions_show", methods="GET")
     */
    public function show(EntityInstitutions $entityInstitution): Response
    {
	if($this->isGranted('POST_VIEW',$entityInstitution)){
        	return $this->render('entity_institutions/show.html.twig', ['entity_institution' => $entityInstitution]);
	}
	else{
		return $this->render('error403forbidden.html.twig');
	}
	return $this->redirectToRoute('entity_institutions_index');
    }

    /**
     * @Route("/{id}/edit", name="entity_institutions_edit", methods="GET|POST")
     */
    public function edit(Request $request, EntityInstitutions $entityInstitution): Response
    {
        if($this->isGranted('POST_EDIT',$entityInstitution)){
            $form = $this->createForm(EntityInstitutionsType::class, $entityInstitution);
            $form->handleRequest($request);
            $mongoman = new MongoManager();

            if ($form->isSubmitted() && $form->isValid()) {

                if (null != $request->request->get('institution_data')) {
                    $dataId = $entityInstitution->getSheetId();


                    foreach ($request->request->get('institution_data') as $key => $value) {
                        if ($value != '') {
                            $mongoman->updateSingleValueById("Entity_institution_sheet", $dataId, $key, $value);
                        } else {
                            $mongoman->unsetSingleValueById("Entity_institution_sheet", $dataId, $key);
                        }
                    }
                }

                $this->getDoctrine()->getManager()->flush();

                return $this->redirectToRoute('entity_institutions_index', ['id' => $entityInstitution->getId()]);
            }
            return $this->render('entity_institutions/edit.html.twig', [
                'entity_institution' => $entityInstitution,
                'form' => $form->createView(),
                'entity_institution_data' => $mongoman->getDocById("Entity_institution_sheet", $entityInstitution->getSheetId()),
            ]);

        }
        else{
            return $this->render('error403forbidden.html.twig');
        }
        return $this->redirectToRoute('entity_institutions_index');
    }

    /**
     * @Route("/{id}", name="entity_institutions_delete", methods="DELETE")
     */
    public function delete(Request $request, EntityInstitutions $entityInstitution): Response
    {
	if($this->isGranted('POST_EDIT',$entityInstitution)){
		if ($this->isCsrfTokenValid('delete'.$entityInstitution->getId(), $request->request->get('_token'))) {
		    $em = $this->getDoctrine()->getManager();
		    $mongoman = new MongoManager();
		    $mongoman->deleteSingleById("Entity_institution_sheet",$entityInstitution->getSheetId());
		    $em->remove($entityInstitution);
		    $em->flush();
		}
	}
	else{
		return $this->render('error403forbidden.html.twig');
	}
        return $this->redirectToRoute('entity_institutions_index');
    }
}
