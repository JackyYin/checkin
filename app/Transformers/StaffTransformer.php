<?php 

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Transformers\StaffTransformer;
use App\Models\Staff;

class StaffTransformer extends TransformerAbstract
{
    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected $availableIncludes = [
        'profile'
    ];

    /**
     * Turn this item object into a generic array
     *
     * @return array
     */
    public function transform(Staff $staff)
    {
        return [
            'id'          => (int) $staff->id,
            'name'        => ($staff->name) ? $staff->name : '',
            'email'       => ($staff->email) ? $staff->email : '',
            'staff_code'  => ($staff->staff_code) ? $staff->staff_code : '',
            'is_admin'    => (bool) $staff->admin ? true : false,
            'is_manager'  => (bool) $staff->manager ? true : false,
            
        ];
    }
    /**
     * Include Profile
     *
     */
    public function includeProfile(Staff $staff)
    {
        if ($profile = $staff->profile) {
            return $this->item($profile, new ProfileTransformer());
        }
        return null;
    }
}
