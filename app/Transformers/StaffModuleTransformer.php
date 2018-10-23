<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\Module;

class StaffModuleTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Module $module)
    {
        return [
            'id'   => $module->id,
            'name' => ($module->name) ? $module->name : '',
            'description' => ($module->description) ? $module->description : '',
            'active' => request()->user()->modules()->find($module->id) ? true : false
        ];
    }
}
