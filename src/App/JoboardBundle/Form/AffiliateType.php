<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 23.10.16
 * Time: 16:54
 */

namespace App\JoboardBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use App\JoboardBundle\Entity\Affiliate;
use App\JoboardBundle\Entity\Category;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class AffiliateType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('url')
            ->add('email')
            ->add('categories', null, ['expanded' => true])
            ->add('_token', HiddenType::class)
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Affiliate::class,
        ]);
    }

    public function getName()
    {
        return 'affiliate';
    }
}