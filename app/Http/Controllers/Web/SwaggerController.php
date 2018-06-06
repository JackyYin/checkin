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
 *     @SWG\SecurityScheme(
 *          securityDefinition="oauth",
 *          type="apiKey",
 *          in="header",
 *          name="Authorization"
 *      )
*/
class SwaggerController extends Controller
{
   public function getJSON()
    {
        $swagger = \Swagger\scan(app_path('Http/Controllers/'));

        return response()->json($swagger, 200);
    }

}
