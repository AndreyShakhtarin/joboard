<?php
namespace App\JoboardBundle\Slug;



class Joboard
{
    public static function slugify($text)
    {

        $text = transliterator_transliterate("Any-Latin; NFD; [:Nonspacing Mark:] Remove; NFC; [:Punctuation:] Remove; Lower();", $text);
        // replace all non letters or digits by -
        $text = preg_replace('/ +/', '-', $text);

        // trim and lowercase
        $text = strtolower(trim($text, '-'));

        if(empty($text)){
            return 'n-a';
        }
        return $text;
    }
}