<?php

namespace Tests;

use App\Models\User;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function defineEnvironment($app): void
    {
        $app['auth']->provider('eloquent_ffe_test', function ($app, array $config) {
            return new class($app['hash'], User::class) extends EloquentUserProvider {
                public function retrieveByCredentials(array $credentials)
                {
                    if (isset($credentials['uid'])) {
                        $credentials['username'] = $credentials['uid'];
                        unset($credentials['uid']);
                    }
                    return parent::retrieveByCredentials($credentials);
                }

                public function validateCredentials(
                    \Illuminate\Contracts\Auth\Authenticatable $user,
                    array $credentials
                ): bool {
                    if (!$user->activo) {
                        return false;
                    }
                    return parent::validateCredentials($user, $credentials);
                }
            };
        });

        $app['config']->set('auth.providers.users', [
            'driver' => 'eloquent_ffe_test',
            'model'  => User::class,
        ]);
    }
}
