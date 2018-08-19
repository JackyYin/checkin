<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\Profile;

class ProfileTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Profile $profile)
    {
        return [
            'ID_card_number'          => ($profile->ID_card_number) ? $profile->ID_card_number : '',
            'identity'                => Profile::getEnum('identity')[$profile->identity],
            'gender'                  => ($profile->gender) ? Profile::getEnum('gender')[$profile->gender] : '',
            'phone_number'            => ($profile->phone_number) ? $profile->phone_number : '',
            'home_address'            => ($profile->home_address) ? $profile->home_address : '',
            'mailing_address'         => ($profile->mailing_address) ? $profile->mailing_address : '',
            'bank_account'            => ($profile->bank_account) ? $profile->bank_account : '',
            'emergency_contact'       => ($profile->emergency_contact) ? $profile->emergency_contact : '',
            'emergency_contact_phone' => ($profile->emergency_contact_phone) ? $profile->emergency_contact_phone : '',
            'position'                => ($profile->position) ? $profile->position : '',
            'on_board_date'           => ($profile->on_board_date) ? $profile->on_board_date->toDateString() : '',
            'off_board_date'          => ($profile->off_board_date) ? $profile->off_board_date : '',
            'birth'                   => ($profile->birth) ? $profile->birth : '',
            'salary'                  => ($profile->salary) ? $profile->salary : '',
            'add_insurance_date'      => ($profile->add_insurance_date) ? $profile->add_insurance_date : '',
            'cancel_insurance_date'   => ($profile->cancel_insurance_date) ? $profile->cancel_insurance_date : '',
            'highest_education'       => ($profile->highest_education) ? $profile->highest_education : '',
            'experience'              => ($profile->experience) ? $profile->experience : '',
            'group'                   => ($profile->group) ? $profile->group : '',
        ];
    }
}
