<?php

namespace Tests\Api\V2\Staff;

use App\Models\Profile;
use App\Models\Staff;
use App\Transformers\StaffTransformer;
use DB;
use League\Fractal\Serializer\ArraySerializer;
use Tests\TestCase;

class MeTest extends TestCase
{
    const METHOD = 'GET';
    const ROUTE = 'api/v2/staff/me';

    /** 成功回傳兩百 */
    public function testSuccess()
    {
        $faker = \Faker\Factory::create();
        DB::beginTransaction();
        $user = factory(Staff::class)->create();

        $profile = Profile::create([
            'staff_id' => $user->id,
            'staff_code' => $faker->regexify('[a-zA-z]{1}[0-9]{3}'),
            'email' => $faker->email,
            'ID_card_number' => $faker->regexify('^[a-zA-Z]{1}[abcdABCD]{1}[0-9]{8}$|^[a-zA-Z]{1}[1-2]{1}[0-9]{8}$')
        ]);

        $response = $this->actingAs($user, 'api')->json(self::METHOD, url(self::ROUTE));
        $response->assertStatus(200)
            ->assertJsonStructure(
                $this->response([
                    "id",
                    "name",
                    "email",
                    "staff_code",
                    "is_admin",
                    "is_manager",
                    "profile" => [
                      "ID_card_number",
                      "identity",
                      "gender",
                      "phone_number",
                      "home_address",
                      "mailing_address",
                      "bank_account",
                      "emergency_contact",
                      "emergency_contact_phone",
                      "position",
                      "on_board_date",
                      "off_board_date",
                      "birth",
                      "salary",
                      "add_insurance_date",
                      "cancel_insurance_date",
                      "highest_education",
                      "experience",
                      "group"
                    ]
                ])
            );
        DB::rollBack();
    }
}
