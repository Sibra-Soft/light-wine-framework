<?php
namespace LightWine\Modules\Files\Models;

class ImageFileReturnModel
{
    public FileReturnModel $File;

    public string $Name;

    public int $Width;
    public int $Height;
    public int $ItemId;

    /**
     * Returns the current image as base64 string
     * @return string The base64 string of the image
     */
    public function Base64(): string {
        return base64_encode($this->File->Blob);
    }

    /**
     * This function rotates the image based on the specified degree
     * @param int $rotateDegrees The amount of degrees you want to rotate
     * @return string Data of the image
     */
    public function Rotate(int $rotateDegrees = 180): string {
        $source = imagecreatefromstring($this->File->Blob);
        $rotate = imagerotate($source, $rotateDegrees, 0);

        return $rotate;
    }

    public function Resize(){
        
    }
}