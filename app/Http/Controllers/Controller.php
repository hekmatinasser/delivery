<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

 /**
 * @OA\Info(
 *     version="1.0",
 *     title="Snapp API"
 * )
 *
 * @OA\PathItem(path="/api")
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
