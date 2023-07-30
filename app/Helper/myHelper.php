<?php


use Illuminate\Support\Facades\Storage;

function checkNationalcode($code)
{
    if (!preg_match('/^[0-9]{10}$/', $code))
        return false;
    for ($i = 0; $i < 10; $i++)
        if (preg_match('/^' . $i . '{10}$/', $code))
            return false;
    for ($i = 0, $sum = 0; $i < 9; $i++)
        $sum += ((10 - $i) * intval(substr($code, $i, 1)));
    $ret = $sum % 11;
    $parity = intval(substr($code, 9, 1));
    if (($ret < 2 && $ret == $parity) || ($ret >= 2 && $ret == 11 - $parity))
        return true;
    return false;
}


function uploadNationalImageToS3($image)
{
    try {
        // Generate a unique filename for the image
        $filename = time() . '_' . $image->getClientOriginalName();

        // Store the image in the 'images' directory on S3
        Storage::disk('liara')->put('images/national_photos/' . $filename, file_get_contents($image));

        // Return the path of the uploaded image
        return 'images/national_photos/' . $filename;
    } catch (Exception $th) {
        throw new Error($th->getMessage());
    }
}



function uploadPublicImageToS3($image, $path = '')
{
    try {
        // Generate a unique filename for the image
        $filename = time() . '_' . $image->getClientOriginalName();

        Storage::disk('liara')->put('images/' . $path . $filename, file_get_contents($image));

        // Return the path of the uploaded image
        return 'images/' . $path . $filename;
    } catch (Exception $th) {
        throw new Error($th->getMessage());
    }
}
