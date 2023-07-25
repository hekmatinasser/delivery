<?php

use Illuminate\Support\Facades\Storage;


function uploadNationalImageToS3($image)
{
    // Generate a unique filename for the image
    $filename = time() . '_' . $image->getClientOriginalName();

    // Store the image in the 'images' directory on S3
    Storage::disk('liara')->put('images/national_photos/' . $filename, file_get_contents($image));

    // Return the path of the uploaded image
    return 'images/national_photos/' . $filename;
}

