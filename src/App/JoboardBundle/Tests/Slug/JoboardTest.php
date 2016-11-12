<?php
namespace App\JoboardBundle\Tests\Slug;

use App\JoboardBundle\Slug\Joboard;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class JoboardTest extends WebTestCase
{
    public function testSlugify()
    {
        $this->assertEquals('company', Joboard::slugify('Company'));
        $this->assertEquals('ooo-company', Joboard::slugify('ooo company'));
        $this->assertEquals('company', Joboard::slugify(' company'));
        $this->assertEquals('company', Joboard::slugify('company '));
        $this->assertEquals('n-a', Joboard::slugify(''));
        $this->assertEquals('n-a', Joboard::slugify(''));
        $this->assertEquals('developpeur-web', Joboard::slugify('Développeur Web'));
    }
}
