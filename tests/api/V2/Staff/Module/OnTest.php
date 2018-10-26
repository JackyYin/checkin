<?php

namespace Tests\Api\V2\Staff\Module;

use App\Models\Module;
use App\Models\Staff;
use DB;
use Tests\TestCase;

class OnTest extends TestCase
{
    const METHOD = 'POST';
    const ROUTE = 'api/v2/staff/module/on';

    /** 成功回傳兩百 */
    public function testSuccess()
    {
        DB::beginTransaction();
        $user = factory(Staff::class)->create();
        $module = factory(Module::class)->create();

        $response = $this->actingAs($user, 'api')->json(self::METHOD, url(self::ROUTE), [
            'module_name' => $module->name
        ]);

        $response->assertStatus(200)->assertExactJson($this->response($module->name."模組已啟用"));

        $this->assertDatabaseHas('staff_module', [
            'staff_id' => $user->id,
            'module_id' => $module->id
        ]);
    }
}
