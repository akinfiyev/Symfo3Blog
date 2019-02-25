<?php
/**
 * Created by PhpStorm.
 * User: q
 * Date: 15.01.19
 * Time: 8:54
 */

namespace AppBundle\Service;

use Exception;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploaderService
{
    private $avatarDirectory;
    private $thumbnailDirectory;

    public function __construct($avatarDirectory, $thumbnailDirectory)
    {
        $this->avatarDirectory = $avatarDirectory;
        $this->thumbnailDirectory = $thumbnailDirectory;
    }

    public function uploadAvatar(UploadedFile $file)
    {
        return $this->upload($file, $this->avatarDirectory);
    }

    public function uploadThumbnail(UploadedFile $file)
    {
        return $this->upload($file, $this->thumbnailDirectory);
    }

    private function upload(UploadedFile $file, $dirName)
    {
        $fileName = md5(uniqid()).'.'.$file->guessExtension();

        try {
            $file->move($dirName, $fileName);
        } catch (FileException $e) {
            throw new Exception('Cant move file to target directory.');
        }

        return $fileName;
    }
}