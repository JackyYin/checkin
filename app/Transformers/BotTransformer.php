<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\Bot;

class BotTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Bot $bot)
    {
        return [
            'id'              => $bot->id,
            'name'            => ($bot->name) ? $bot->name : '',
            'auth_hook_url'   => ($bot->auth_hook_url) ? $bot->auth_hook_url : '',
            'notify_hook_url' => ($bot->notify_hook_url) ? $bot->notify_hook_url : ''
        ];
    }
}
