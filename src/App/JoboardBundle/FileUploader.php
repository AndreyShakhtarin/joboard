<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 31.08.16
 * Time: 18:09
 */

namespace App\JoboardBundle;


use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{

     private  $targetDir;

    public function __construct($targetDir)
    {
        $this->targetDir = $targetDir;
    }

    public function upload(UploadedFile $file)
    {
        $fileName = md5(uniqid()).'.'.$file->guessExtension();

        $file->move($this->targetDir, $fileName);

        return $fileName;
    }
}