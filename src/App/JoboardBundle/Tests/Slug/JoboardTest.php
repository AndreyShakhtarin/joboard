<?php
namespace App\JoboardBundle\Tests\Slug;

use App\JoboardBundle\Slug;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class JoboardTest extends WebTestCase
{
    public function testSlugify()
    {
        $this->assertEquals('company', Slug\Joboard::slugify('Company'));
        $this->assertEquals('ooo-company', Slug\Joboard::slugify('ooo company'));
        $this->assertEquals('company', Slug\Joboard::slugify(' company'));
        $this->assertEquals('company', Slug\Joboard::slugify('company '));
        $this->assertEquals('n-a', Joboard::slugify(''));
    }
}
