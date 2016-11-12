<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 23.10.16
 * Time: 21:47
 */

namespace App\JoboardBundle\Admin;

use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use App\JoboardBundle\Entity\Affiliate;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Route\RouteCollection;

class AffiliateAdmin extends AbstractAdmin
{

    protected $datagridValues = [
        '_sort_order' => 'ASC',
        '_sort_by'    => 'is_active',
        'is_active'   => ['value' => 2] // 2 показывает только неактивированные записи
    ];

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('email')
            ->add('url')
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('email')
            ->add('is_active');
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('is_active')
            ->addIdentifier('email')
            ->add('url')
            ->add('created_at')
            ->add('token')
            ->add('_action', 'actions', [
                'actions' => [
                    'activate'   => ['template' => 'AppJoboardBundle:AffiliateAdmin:list__action_activate.html.twig'],
                    'deactivate' => ['template' => 'AppJoboardBundle:AffiliateAdmin:list__action_deactivate.html.twig']
                ]
            ])
        ;
    }

    public function getBatchActions()
    {
        $actions = parent::getBatchActions();

        if($this->hasRoute('edit') && $this->isGranted('EDIT') && $this->hasRoute('delete') && $this->isGranted('DELETE')) {
            $actions['activate'] = [
                'label'            => 'Активировать',
                'ask_confirmation' => true
            ];

            $actions['deactivate'] = [
                'label'            => 'Деактивировать',
                'ask_confirmation' => true
            ];
        }

        return $actions;
    }

    protected function configureRoutes(RouteCollection $collection) {
        parent::configureRoutes($collection);

        $collection->add('activate',
            $this->getRouterIdParameter().'/activate')
        ;

        $collection->add('deactivate',
            $this->getRouterIdParameter().'/deactivate')
        ;
    }
}