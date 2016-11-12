<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 23.10.16
 * Time: 13:32
 */

namespace App\JoboardBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use App\JoboardBundle\Entity\Affiliate;

class LoadAffiliateData extends AbstractFixture implements OrderedFixtureInterface
{

    public function load(ObjectManager $em)
    {
        $affiliate = new Affiliate();

        $affiliate->setUrl('http://example.com/');
        $affiliate->setEmail('address1@example.com');
        $affiliate->setToken('example');
        $affiliate->setIsActive(true);
        $affiliate->addCategory($em->merge($this->getReference('category-programming')));

        $em->persist($affiliate);

        $affiliate = new Affiliate();

        $affiliate->setUrl('/');
        $affiliate->setEmail('address2@example.org');
        $affiliate->setToken('symfony');
        $affiliate->setIsActive(false);
        $affiliate->addCategory($em->merge($this->getReference('category-programming')), $em->merge($this->getReference('category-design')));

        $em->persist($affiliate);
        $em->flush();

        $this->addReference('affiliate', $affiliate);
    }

    public function getOrder()
    {
        return 3; // Порядок в котором будут загружены фикстуры
    }
}