<?php

namespace Tests\Api\V2\Leave;

use App\Models\Check;
use App\Models\Staff;
use DB;
use Tests\TestCase;

class GetLeaveTypeTest extends TestCase
{
    const METHOD = 'GET';
    const ROUTE = 'api/v2/leave/types';

    /** 成功回傳兩百 */
    public function testSuccess()
    {
        DB::beginTransaction();
        $user = factory(Staff::class)->create();

        $data = [];

        foreach (array_except(Check::getEnum('type'), Check::TYPE_NORMAL) as $key => $value) {
            $data[] = [
                'id' => $key,
                'name' => $value
            ];
        };

        $response = $this->actingAs($user, 'api')->json(self::METHOD, url(self::ROUTE));
        $response->assertStatus(200)->assertExactJson($this->response($data));
    }
}
