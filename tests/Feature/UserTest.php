<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use function PHPUnit\Framework\assertNotEquals;
use function PHPUnit\Framework\assertNotNull;
use function PHPUnit\Framework\assertNull;

class UserTest extends TestCase
{
    public function testRegisterSuccess()
    {
        $this->post('/api/users', [
            'username' => 'khannedy',
            'password' => 'rahasia',
            'name' => 'Eko Kurniawan Khannedy'
        ])->assertStatus(201)
            ->assertJson([
                "data" => [
                    'username' => 'khannedy',
                    'name' => 'Eko Kurniawan Khannedy'
                ]
            ]);
    }

    public function testRegisterFailed()
    {
        $this->post('/api/users', [
            'username' => '',
            'password' => '',
            'name' => ''
        ])->assertStatus(400)
            ->assertJson([
                "errors" => [
                    'username' => [
                        "The username field is required."
                    ],
                    'password' => [
                        "The password field is required."
                    ],
                    'name' => [
                        "The name field is required."
                    ]
                ]
            ]);
    }

    public function testRegisterUsernameAlreadyExist()
    {
        $this->testRegisterSuccess();
        $this->post('/api/users', [
            'username' => 'khannedy',
            'password' => 'rahasia',
            'name' => 'Eko Kurniawan Khannedy'
        ])->assertStatus(400)
            ->assertJson([
                "errors" => [
                    'username' => [
                        "Username already registered"
                    ]
                ]
            ]);
    }

    public function testLoginSuccess()
    {
        $this->seed([UserSeeder::class]);
        $this->post('/api/users/login', [
            'username' => 'test',
            'password' => 'test',
        ])->assertStatus(200)
            ->assertJson([
                'data' => [
                    'username' => 'test',
                    'name' => 'test'
                ]
            ]);

        $user = User::where('username', 'test')->first();
        assertNotNull($user->token);
    }

    public function testLoginFailedUsernameNotFound()
    {
        $this->post('/api/users/login', [
            'username' => 'tests',
            'password' => 'test',
        ])->assertStatus(401)
            ->assertJson([
                'errors' => [
                    'message' => [
                        'Username or password wrong'
                    ]
                ]
            ]);
    }

    public function testLoginFailedPasswordWrong()
    {
        $this->seed([UserSeeder::class]);
        $this->post('/api/users/login', [
            'username' => 'test',
            'password' => 'tests',
        ])->assertStatus(401)
            ->assertJson([
                'errors' => [
                    'message' => [
                        'Username or password wrong'
                    ]
                ]
            ]);
    }

    public function testGetSuccess()
    {
        $this->seed([UserSeeder::class]);

        $this->get('/api/users/current', [
            'Authorization' => 'test'
        ])->assertStatus(200)
            ->assertjson([
                'data' => [
                    'username' => 'test',
                    'name' => 'test'
                ]
            ]);
    }

    public function testGetUnathorized()
    {
        $this->seed([UserSeeder::class]);

        $this->get('/api/users/current')
            ->assertStatus(401)
            ->assertjson([
                'errors' => [
                    'message' => [
                        'unathorized'
                    ]
                ]
            ]);
    }

    public function testGetInvalidToken()
    {
        $this->seed([UserSeeder::class]);

        $this->get('/api/users/current', [
            'Authorization' => 'salah'
        ])->assertStatus(401)
            ->assertjson([
                'errors' => [
                    'message' => [
                        'unathorized'
                    ]
                ]
            ]);
    }

    public function testUpdatePasswordSuccess()
    {
        $this->seed([UserSeeder::class]);
        $oldUser = User::where('username', 'test')->first();

        $this->patch(
            '/api/users/current',
            [
                "password" => "baru"
            ],
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(200)
            ->assertjson([
                'data' => [
                    'username' => 'test',
                    'name' => 'test'
                ]
            ]);

        $newUser = User::where('username', 'test')->first();
        self::assertNotEquals($oldUser->password, $newUser->password);
    }

    public function testUpdateNameSuccess()
    {
        $this->seed([UserSeeder::class]);
        $oldUser = User::where('username', 'test')->first();

        $this->patch(
            '/api/users/current',
            [
                "name" => "Eko"
            ],
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(200)
            ->assertjson([
                'data' => [
                    'username' => 'test',
                    'name' => 'Eko'
                ]
            ]);

        $newUser = User::where('username', 'test')->first();
        self::assertNotEquals($oldUser->name, $newUser->name);
    }

    public function testUpdatePasswordFailed()
    {
        $this->seed([UserSeeder::class]);

        $this->patch(
            '/api/users/current',
            [
                "password" => "EkoaksdjfksladjfklajsdfEkoaksdjfksladjfklajsdfEkoaksdjEkoaksdjfksladjfklajsdfEkoaksdjfksladjfklajsdfEkoaksdjfksladjfklajsdfEkoaksdjfksladjfklajsdfEkoaksdjfksladjfklajsdfEkoaksdjfksladjfklajsdfEkoaksdjfksladjfklajsdffksladjfklajsdfEkoaksdjfksladjfklajsdfEkoaksdjfksladjfklajsdfEkoaksdjfksladjfklajsdfEkoaksdjfksladjfklajsdfEkoaksdjfksladjfklajsdfEkoaksdjfksladjfklajsdfEkoaksdjfksladjfklajsdfEkoaksdjfksladjfklajsdfEkoaksdjfksladjfklajsdf"
            ],
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(400)
            ->assertjson([
                'errors' => [
                    'password' => [
                        'The password field must not be greater than 100 characters.'
                    ]
                ]
            ]);
    }

    public function testUpdateNameFailed()
    {
        $this->seed([UserSeeder::class]);

        $this->patch(
            '/api/users/current',
            [
                "name" => "EkoaksdjfksladjfklajsdfEkoaksdjfksladjfklajsdfEkoaksdjEkoaksdjfksladjfklajsdfEkoaksdjfksladjfklajsdfEkoaksdjfksladjfklajsdfEkoaksdjfksladjfklajsdfEkoaksdjfksladjfklajsdfEkoaksdjfksladjfklajsdfEkoaksdjfksladjfklajsdffksladjfklajsdfEkoaksdjfksladjfklajsdfEkoaksdjfksladjfklajsdfEkoaksdjfksladjfklajsdfEkoaksdjfksladjfklajsdfEkoaksdjfksladjfklajsdfEkoaksdjfksladjfklajsdfEkoaksdjfksladjfklajsdfEkoaksdjfksladjfklajsdfEkoaksdjfksladjfklajsdf"
            ],
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(400)
            ->assertjson([
                'errors' => [
                    'name' => [
                        'The name field must not be greater than 100 characters.'
                    ]
                ]
            ]);
    }

    public function testLogoutSuccess()
    {
        $this->seed([UserSeeder::class]);

        $this->delete(uri: '/api/users/logout', headers: [
            'Authorization' => 'test'
        ])->assertStatus(200)
            ->assertJson([
                "data" => [
                    "true"
                ]
            ]);

        $user = User::where('username', 'test')->first();
        self::assertNull($user->token);
    }

    public function testLogoutFailed()
    {
        $this->seed([UserSeeder::class]);

        $this->delete('/api/users/logout', [
            'Authorization' => 'salah'
        ])->assertStatus(401)
            ->assertJson([
                "errors" => [
                    "message" => [
                        "unathorized"
                    ]
                ]
            ]);
    }
}
