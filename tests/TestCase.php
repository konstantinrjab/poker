<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Auth;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function put($uri, array $data = [], array $headers = [])
    {
        $this->clearAuth();
        return parent::put($uri, $data, $headers);
    }

    protected function clearAuth(): void
    {
        // TODO: find better solution
        $authGuard = Auth::guard('api');

        $reflection = new \ReflectionProperty(get_class($authGuard), 'user');
        $reflection->setAccessible(true);
        $reflection->setValue($authGuard, null);
    }
}
