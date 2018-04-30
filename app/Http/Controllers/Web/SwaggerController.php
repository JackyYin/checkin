<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
/**
 * @SWG\Swagger(
 *   @SWG\Info(
 *     title="我的`Swagger`API",
 *     version="1.0.0"
 *   )
 * )
*/
class SwaggerController extends Controller
{
   public function getJSON()
    {
        $swagger = \Swagger\scan(app_path('Http/Controllers/'));

        return response()->json($swagger, 200);
    }

}
