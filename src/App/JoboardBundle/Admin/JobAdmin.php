<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 19.10.16
 * Time: 22:37
 */

namespace App\JoboardBundle\Admin;


use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Validator\ErrorElement;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use App\JoboardBundle\Entity\Job;

class JobAdmin extends Admin
{
// установка сортировки по умолчанию
    protected $datagridValues = [
        '_sort_order' => 'DESC',
        '_sort_by' => 'created_at'
    ];

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('category')
            ->add('type', 'choice', ['choices' => Job::getTypes(), 'expanded' => true])
            ->add('company')
            ->add('file', 'file', ['label' => 'Лого компании', 'required' => false])
            ->add('url')
            ->add('position')
            ->add('location')
            ->add('description')
            ->add('how_to_apply')
            ->add('is_public')
            ->add('email')
            ->add('is_activated')
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('category')
            ->add('company')
            ->add('position')
            ->add('description')
            ->add('is_activated')
            ->add('is_public')
            ->add('email')
            ->add('expires_at')
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('company')
            ->add('position')
            ->add('location')
            ->add('url')
            ->add('is_activated')
            ->add('email')
            ->add('category')
            ->add('expires_at')
            ->add('_action', 'actions', [
                'actions' => [
                    'view' => [],
                    'edit' => [],
                    'delete' => [],
                ]
            ])
        ;
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('category')
            ->add('type')
            ->add('company')
            ->add('webPath', 'string', ['template' => 'AppJoboardBundle:JobAdmin:list_image.html.twig'])
            ->add('url')
            ->add('position')
            ->add('location')
            ->add('description')
            ->add('how_to_apply')
            ->add('is_public')
            ->add('is_activated')
            ->add('token')
            ->add('email')
            ->add('expires_at')
        ;
    }

    public function getBatchActions()
    {
        $actions = parent::getBatchActions();

        // проверка прав пользователя
        if ($this->hasRoute('edit') && $this->isGranted('EDIT') && $this->hasRoute('delete') && $this->isGranted('DELETE')) {
            $actions['extend'] = [
                'label' => 'Продлить',
                'ask_confirmation' => true // Если true, будет выведено сообщение о подтверждении действия
            ];
        }

        $actions['deleteNeverActivated'] = [
            'label'            => 'Удалить просроченные вакансии',
            'ask_confirmation' => true // Если true, будет выведено сообщение о подтверждении действия
        ];

        return $actions;
    }

}