<?php
namespace App\Service;

class ImageService
{

    public function compress($source_url, $quality = 75)
    {
        $info = getimagesize($source_url);
        if ($info['mime'] == 'image/jpeg') {
            $image = imagecreatefromjpeg($source_url);
        } elseif ($info['mime'] == 'image/gif') {
            $image = imagecreatefromgif($source_url);
        } elseif ($info['mime'] == 'image/png') {
            $image = imagecreatefrompng($source_url);
        }
        imagejpeg($image, $source_url, $quality);
        return $source_url;
    }
}