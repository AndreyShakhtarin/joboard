<?php

namespace App\JoboardBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File;



use App\JoboardBundle\Entity\Job;
use App\JoboardBundle\Form\JobType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Job controller.
 *
 */
class JobController extends Controller
{
    /**
     *
     *
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $categories = $em->getRepository('AppJoboardBundle:Category')->getWithJobs();

        foreach($categories as $category) {
            $category->setActiveJobs($em->getRepository('AppJoboardBundle:Job')->getActiveJobs(
                $category->getId(),
                $this->container->getParameter('max_jobs_on_homepage'))
            );

            $activeJobsCount = $em->getRepository('AppJoboardBundle:Job')->countActiveJobs($category->getId());

            if ($activeJobsCount >= $this->container->getParameter('max_jobs_on_homepage')) {
                $activeJobsCount -= $this->container->getParameter('max_jobs_on_homepage');
                $category->setMoreJobs($activeJobsCount);
            }
        }

        $latestJob = $em->getRepository('AppJoboardBundle:Job')->getLatestPost();

        if($latestJob) {
            $lastUpdated = $latestJob->getCreatedAt()->format(DATE_ATOM);
        } else {
            $lastUpdated = new \DateTime();
            $lastUpdated = $lastUpdated->format(DATE_ATOM);
        }

        $_format = $request->query->getAlnum('_format');
        if(!$_format){
            $_format = 'html';
        }

        return $this->render(
            'AppJoboardBundle:Job:index.'.$_format.'.twig',
            [
                'categories'  => $categories,
                'lastUpdated' => $lastUpdated,
                'feedId'      => sha1($this->generateUrl('app_job_index', ['_format'=> 'atom'], true))
            ]);
    }


    /**
     * Creates a new Job entity.
     *
     */
    public function newAction(Request $request)
    {
        $entity = new Job();
        $form = $this->createForm(JobType::class, $entity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entity->file->move(__DIR__.'/../../../../web/uploads/jobs', $entity->file->getClientOriginalName());
            $entity->setLogo($entity->file->getClientOriginalName());
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirectToRoute('app_job_show', array(
                'id' => $entity->getId(),
                "company" => $entity->getCompany(),
                "position" => $entity->getPosition(),
                "location" => $entity->getLocation(),
                'token' => $entity->getToken()
            ));

        }

        return $this->render('AppJoboardBundle:Job:new.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView(),
        ));
    }



    /**
     * Finds and displays a Job entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AppJoboardBundle:Job')->getActiveJob($id);
        if(!$entity)
        {
            throw $this->createNotFoundException('Unable to find Job entity.');
        }
        $session = $this->get('session');
        $jobs = $session->get('job_history', []);
        $job = [
            'id' => $entity->getId(),
            'position' =>$entity->getPosition(),
            'company' => $entity->getCompany(),
            'companyslug' => $entity->getCompanySlug(),
            'locationslug' => $entity->getLocationSlug(),
            'positionslug' => $entity->getPositionSlug()
        ];
        if (!in_array($job, $jobs)) {
            // добавить текущую вакансию в начало массива
            array_unshift($jobs, $job);

            // обновить истории посещений
            $session->set('job_history', array_slice($jobs, 0, 3));
        }
        $deleteForm = $this->createDeleteForm($job);
        //$job = $em->getRepository('AppJoboardBundle:Job')->getActiveJob($id);
        return $this->render('AppJoboardBundle:Job:show.html.twig', array(
            'entity' => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Job entity.
     *
     */
    public function editAction($token)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AppJoboardBundle:Job')->findOneByToken($token);

        if (!$entity) {
            throw $this->createNotFoundException('Такой вакансии не существует.');
        }

        $editForm = $this->createForm(JobType::class, $entity, [
            'action' => $this->generateUrl('app_job_update', ['token' => $token]),
            'method' => 'PUT',
        ]);
        $deleteForm = $this->createDeleteForm($token);

        return $this->render('AppJoboardBundle:Job:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    public function updateAction(Request $request, $token)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppJoboardBundle:Job')->findOneByToken($token);

        if (!$entity) {
            throw $this->createNotFoundException('Такой вакансии не существует.');
        }

        $editForm   = $this->createForm(JobType::class, $entity, [
            'action' => $this->generateUrl('app_job_update', ['token' => $token]),
            'method' => 'PUT'
        ]);
        $deleteForm = $this->createDeleteForm($token);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('app_job_edit', array('token' => $token)));
        }

        return $this->render('AppJoboardBundle:Job:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Job entity.
     *
     */
    public function deleteAction(Request $request, $token)
    {
        $form = $this->createDeleteForm($token);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('AppJoboardBundle:Job')->findOneByToken($token);

            if (!$entity) {
                throw $this->createNotFoundException('Такой вакансии не существует.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('app_job'));
    }

    /**
     * Creates a form to delete a Job entity.
     *
     * @param Job $job The Job entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($token)
    {
        return $this->createFormBuilder(['token' => $token])
            ->add('token',HiddenType::class, ['csrf_token_id' => $token])
            ->getForm()
            ;
    }

    public function previewAction($token)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AppJoboardBundle:Job')->findOneByToken($token);

        if (!$entity) {
            throw $this->createNotFoundException('Такой вакансии не существует.');
        }

        $deleteForm = $this->createDeleteForm($entity->getId());

        return $this->render('AppJoboardBundle:Job:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }
}
