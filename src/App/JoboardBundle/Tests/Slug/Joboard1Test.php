<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 08.02.16
 * Time: 14:11
 */

namespace Test\App\JoboardBundle\Slug;

use App\JoboardBundle\Slug;


class Joboard1Test extends \PHPUnit_Framework_TestCase
{
    public function testSlugify()
    {
        $this->assertEquals('company', Slug\Joboard::slugify('Company'));
        $this->assertEquals('ooo-company', Slug\Joboard::slugify('ooo company'));
        $this->assertEquals('company', Slug\Joboard::slugify(' company'));
        $this->assertEquals('company', Slug\Joboard::slugify('company '));
        $this->assertEquals('n-a', Slug\Joboard::slugify(''));
        $this->assertEquals('n-a', Slug\Joboard::slugify(' - '));
        //$this->assertEquals('developpeur-web', Slug\Joboard::slugify('Développeur Web'));
    }
}
