<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 04.02.16
 * Time: 17:03
 */

namespace App\JoboardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\JoboardBundle\Entity\Category;
use App\JoboardBundle\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Request;

class CategoryController extends Controller
{
    public function showAction(Request $request, $slug, $page)
    {
        $em = $this->getDoctrine()->getManager();

        $category = $em->getRepository('AppJoboardBundle:Category')->findOneBySlug($slug);

        if (!$category) {
            throw $this->createNotFoundException('Такая категория не найдена.');
        }

        $totalJobs    = $em->getRepository('AppJoboardBundle:Job')->countActiveJobs($category->getId());
        $jobsPerPage  = $this->container->getParameter('max_jobs_on_category');
        $lastPage     = ceil($totalJobs / $jobsPerPage);
        $previousPage = $page > 1 ? $page - 1 : 1;
        $nextPage     = $page < $lastPage ? $page + 1 : $lastPage;
        $activeJobs   = $em->getRepository('AppJoboardBundle:Job')
            ->getActiveJobs($category->getId(), $jobsPerPage, ($page - 1) * $jobsPerPage);

        $category->setActiveJobs($activeJobs);

        $latestJob = $em->getRepository('AppJoboardBundle:Job')->getLatestPost($category->getId());

        if($latestJob) {
            $lastUpdated = $latestJob->getCreatedAt()->format(DATE_ATOM);
        } else {
            $lastUpdated = new \DateTime();
            $lastUpdated = $lastUpdated->format(DATE_ATOM);
        }

        $format = $request->getRequestFormat();

        return $this->render('AppJoboardBundle:Category:show.'.$format.'.twig', array(
            'category'     => $category,
            'lastPage'     => $lastPage,
            'previousPage' => $previousPage,
            'currentPage'  => $page,
            'nextPage'     => $nextPage,
            'totalJobs'    => $totalJobs,
            'feedId'       => sha1($this->generateUrl('AppJoboardBundle_category', ['slug' => $category->getSlug(), 'format' => 'atom'], true)),
            'lastUpdated' => $lastUpdated
        ));
    }
}