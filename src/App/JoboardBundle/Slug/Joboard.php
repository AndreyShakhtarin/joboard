<?php
namespace App\JoboardBundle\Slug;



class Joboard
{
    static public function slugify($text)
    {

        //$text = transliterator_transliterate("Any-Latin; NFD; [:Nonspacing Mark:] Remove; NFC; [:Punctuation:] Remove; Lower();", $text);
        // replace all non letters or digits by -
        $text = preg_replace('/\W+/', '-', $text);

        // trim and lowercase
        $text = strtolower(trim($text, '-'));

        if(empty($text)){
            return 'n-a';
        }
        return $text;
    }
}