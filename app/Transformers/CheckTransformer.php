<?php 

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\Check;

class CheckTransformer extends TransformerAbstract
{
    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected $availableIncludes = [
        'reason', 'staff'
    ];

    /**
     * If contains simple return (not including reason)
     *
     * @var bool
     */
    protected $simple;

    public function __construct($simple = false)
    {
        $this->simple = $simple;
    }
    /**
     * Turn this item object into a generic array
     *
     * @return array
     */
    public function transform(Check $check)
    {
        if ($this->simple && $check->simple()) {
            return [
                'id'          => (int) $check->id,
                'type'        => $check->type,
                'checkin_at'  => $check->checkin_at,
                'checkout_at' => $check->checkout_at,
                'name'        => $check->staff->name,
            ];
        }

        return [
            'id'          => (int) $check->id,
            'type'        => $check->type,
            'checkin_at'  => $check->checkin_at,
            'checkout_at' => $check->checkout_at,
            'reason'      => $check->leave_reason ? $check->leave_reason->reason : '' ,
            'name'        => $check->staff->name,
            
        ];
    }
    /**
     * Include Leave Reason
     *
     */
    public function includeReason(Check $check)
    {
        return $this->item($check, function ($check) {
            return [
                'reason' => $check->leave_reason->reason,
            ];
        });
    }
    /**
     * Include Staff
     *
     */
    public function includeStaff(Check $check)
    {
        return $this->item($check, function ($check) {
            return [
                'name' => $check->staff->name
            ];
        });
    }
}
