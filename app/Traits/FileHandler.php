<?php


namespace App\Traits;


trait FileHandler
{
    /**
     * Upload file
     *
     * @param $file
     * @param $path
     * @return mixed
     */
    public function uploadFile($file, $path)
    {
        $extension = $file->extension();
        $fileName = round(microtime(true) * 1000) . '.' . $extension;
        $file->move(public_path($path), $fileName);
        return $fileName;
    }
}
