<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function response($data = [])
    {
        return [
            'reply_message' => $data
        ];
    }
}
