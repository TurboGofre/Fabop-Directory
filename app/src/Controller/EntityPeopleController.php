<?php

namespace App\Controller;

use App\Entity\EntityPeople;
use App\Entity\Log;
use App\Form\EntityPeopleType;
use App\Repository\EntityInstitutionsRepository;
use App\Repository\EntityPeopleRepository;
use App\Security\Voter\PermissionCalculator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Utils\MongoManager;

use App\Repository\TagsAffectRepository;

/**
 * @Route("/manager/people")
 */
class EntityPeopleController extends AbstractController
{
    /**
     * @Route("/", name="entity_people_index", methods="GET")
     */
    public function index(EntityPeopleRepository $entityPeopleRepository): Response
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        //filtres à appliquer ici

        // le role user ou contibuteur ne peut voir que les entités rattaché à son institution
        if(in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
            $list = PermissionCalculator::checkRight($user, "peoples", $entityPeopleRepository->findAll(),"read");
            $edit = PermissionCalculator::checkRight($user, "peoples", $list,"write");
            return $this->render('entity_people/index.html.twig', ['entity_people' => $list, "edits" => $edit]);
        } else{
            $list = PermissionCalculator::checkRight($user, "peoples", $entityPeopleRepository->findAll(),"read");
            $edit = PermissionCalculator::checkRight($user, "peoples", $list,"write");
            return $this->render('entity_people/index.html.twig', ['entity_people' => $list, "edits" => $edit]);
        }

    }

    /**
     * @Route("/new", name="entity_people_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
	$entityPerson = new EntityPeople();
	if($this->isGranted('POST_EDIT',$entityPerson)){
		if ($this->getDoctrine()->getManager()->getRepository(\App\Entity\EntityInstitutions::class)->countAll()<1){
		    return $this->redirectToRoute('entity_institutions_new');
		}
		$form = $this->createForm(EntityPeopleType::class, $entityPerson);
		$form->handleRequest($request);
		$mongoman = new MongoManager();

		if ($form->isSubmitted() && $form->isValid()) {
		    $em = $this->getDoctrine()->getManager();
		    if($request->request->get("person_data") !== null){
		        foreach($request->request->get("person_data") as $elem){
		            $data[$elem['label']] = $elem['value'];
		        }  
		    }
		    if (isset($data)){
		        $sheetId=$mongoman->insertSingle("Entity_person_sheet",$data);
		    }
		    else{
		        $sheetId=$mongoman->insertSingle("Entity_person_sheet",[]);
		    }
		    // Mise en bdd MySQL de l'ID de fiche de données
		    $entityPerson->setSheetId($sheetId);
		    $entityPerson->setAddDate(new \DateTime("now"));
		    if(!$this->isGranted('ROLE_ADMIN'))
		        $entityPerson->setInstitution($this->getUser()->getInstitution());
		    $em->persist($entityPerson);
		    $em->flush();

		    return $this->redirectToRoute('entity_people_index');
		}

		return $this->render('entity_people/new.html.twig', [
		    'entity_person' => $entityPerson,
		    'form' => $form->createView(),
		]);
	}
	else{
		return $this->render('error403forbidden.html.twig');
	}
	return $this->redirectToRoute('entity_people_index');
    }

    /**
     * @Route("/{id}", name="entity_people_show", methods="GET")
     */
    public function show(EntityPeople $entityPerson, TagsAffectRepository $tagsAffectRepository): Response
    {
	if($this->isGranted('POST_VIEW',$entityPerson)){
		$mongoman = new MongoManager();
		return $this->render('entity_people/show.html.twig', [
		    'entity_person' => $entityPerson,
		    'tags_affects' => $tagsAffectRepository->findAll(),
		    'entity_person_data' => $mongoman->getDocById("Entity_person_sheet",$entityPerson->getSheetId()),
		]);
	}
	else{
		return $this->render('error403forbidden.html.twig');
	}
	return $this->redirectToRoute('entity_people_index');
    }

    /**
     * @Route("/{id}/edit", name="entity_people_edit", methods="GET|POST")
     */
    public function edit(Request $request, EntityPeople $entityPerson): Response
    {
	if($this->isGranted('POST_EDIT',$entityPerson)){
		$form = $this->createForm(EntityPeopleType::class, $entityPerson);
		$form->handleRequest($request);
		$mongoman = new MongoManager();
		$em = $this->getDoctrine()->getManager();

		if ($form->isSubmitted() && $form->isValid()) {
		    if (null != $request->request->get('person_data')){
		        $dataId=$entityPerson->getSheetId();
		        foreach( $request->request->get('person_data') as $key=>$value){
		            if ($value!=''){
		                $mongoman->updateSingleValueById("Entity_person_sheet",$dataId,$key,$value);
		            }else{
		                $mongoman->unsetSingleValueById("Entity_person_sheet",$dataId,$key);
		            }
		        }
		    }
		    $em->flush();

		    return $this->redirectToRoute('entity_people_index', ['id' => $entityPerson->getId()]);
		}

		return $this->render('entity_people/edit.html.twig', [
		    'entity_person' => $entityPerson,
		    'form' => $form->createView(),
		    'entity_person_data' => $mongoman->getDocById("Entity_person_sheet",$entityPerson->getSheetId()),
		]);
	}
	else{
		return $this->render('error403forbidden.html.twig');
	}
	return $this->redirectToRoute('entity_people_index');
    }

    /**
     * @Route("/{id}", name="entity_people_delete", methods="DELETE")
     */
    public function delete(Request $request, EntityPeople $entityPerson): Response
    {
	if($this->isGranted('POST_EDIT',$entityPerson)){

		if ($this->isCsrfTokenValid('delete'.$entityPerson->getId(), $request->request->get('_token'))) {
		    $em = $this->getDoctrine()->getManager();
		    $mongoman = new MongoManager();
		    $mongoman->deleteSingleById("Entity_person_sheet",$entityPerson->getSheetId());

		    $em->remove($entityPerson);
		    $em->flush();
		}
	}
	else{
		return $this->render('error403forbidden.html.twig');
	}

        return $this->redirectToRoute('entity_people_index');
    }
}
