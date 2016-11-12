<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 23.10.16
 * Time: 13:43
 */

namespace App\JoboardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\JoboardBundle\Entity\Affiliate;
use App\JoboardBundle\Entity\Job;
use App\JoboardBundle\Repository\AffiliateRepository;

class ApiController extends Controller
{

    public function listAction(Request $request, $token, $_format)
    {

        $em   = $this->getDoctrine()->getManager();
        $jobs = [];

        $rep       = $em->getRepository('AppJoboardBundle:Affiliate');
        $affiliate = $rep->getForToken($token);

        if (!$affiliate) {
            throw $this->createNotFoundException('Такого партнёра не существует!');
        }

        $rep        = $em->getRepository('AppJoboardBundle:Job');
        $activeJobs = $rep->getActiveJobs(null, null, null, $affiliate->getId());

        foreach ($activeJobs as $job) {
            $jobs[$this->generateUrl('app_job_show', [
                'company'  => $job->getCompanySlug(),
                'location' => $job->getLocationSlug(),
                'id'       => $job->getId(),
                'position' => $job->getPositionSlug()
            ], true)] = $job->asArray($request->getHost());
        }

        $jsonData = json_encode($jobs);
        if ($_format == "json") {
            $headers  = ['Content-Type' => 'application/json'];
            $response = new Response($jsonData, 200, $headers);

            return $response;
        }


        return $this->render('AppJoboardBundle:Api:jobs.' . $_format . '.twig', ['jobs' => $jobs]);
    }
}