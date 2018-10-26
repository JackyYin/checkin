<?php

namespace Tests\Api\V2\Staff\Module;

use App\Models\Module;
use App\Models\Staff;
use DB;
use Tests\TestCase;

class IndexTest extends TestCase
{
    const METHOD = 'GET';
    const ROUTE = 'api/v2/staff/module';

    /** 成功回傳兩百 */
    public function testSuccess()
    {
        DB::beginTransaction();
        $user = factory(Staff::class)->create();
        factory(Module::class)->create();

        $data = [];

        foreach (Module::all() as $module) {
            $data[] = [
                'id'   => $module->id,
                'name' => ($module->name) ? $module->name : '',
                'description' => ($module->description) ? $module->description : '',
                'active' => $user->modules()->find($module->id) ? true : false
            ];
        };

        $response = $this->actingAs($user, 'api')->json(self::METHOD, url(self::ROUTE));
        $response->assertStatus(200)->assertExactJson($this->response(['data' => $data]));
        DB::rollBack();
    }
}
