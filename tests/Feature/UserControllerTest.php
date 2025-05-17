<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpFaker();
    }

    protected function authenticate()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);
        return ['Authorization' => 'Bearer ' . $token];
    }

    public function test_user_can_login_with_valid_credentials()
    {
        $password = 'senha123';
        $user = User::factory()->create([
            'password' => Hash::make($password),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'user', 'auth' => ['token', 'type']]);
    }

    public function test_user_cannot_login_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'password' => Hash::make('senhaCorreta'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'senhaErrada',
        ]);

        $response->assertStatus(401)
            ->assertJsonFragment(['message' => 'Não autorizado']);
    }

    public function test_authenticated_user_can_logout()
    {
        $headers = $this->authenticate();

        $response = $this->postJson('/api/logout', [], $headers);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Logout realizado com sucesso.']);
    }

    public function test_token_can_be_refreshed()
    {
        $headers = $this->authenticate();

        $response = $this->postJson('/api/refresh', [], $headers);

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'user', 'auth' => ['token', 'type']]);
    }

    public function test_can_list_users()
    {
        User::factory()->count(3)->create();
        $headers = $this->authenticate();

        $response = $this->postJson('/api/user/index', [], $headers);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [['id', 'name', 'email']]
            ]);
    }

    public function test_can_create_user()
    {
        $headers = $this->authenticate();

        $data = [
            'name' => $this->faker->firstName,
            'lastname' => $this->faker->lastName,
            'phone' => $this->faker->phoneNumber,
            'email' => $this->faker->unique()->safeEmail,
            'password' => 'senha123',
        ];

        Cache::shouldReceive('store')->once()->with('redis')->andReturnSelf();
        Cache::shouldReceive('forget')->once()->with('users:all');

        $response = $this->postJson('/api/user', $data, $headers);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Usuário criado com sucesso']);
    }

    public function test_can_show_user()
    {
        $user = User::factory()->create();
        $headers = $this->authenticate();

        $response = $this->getJson("/api/user/{$user->id}", $headers);

        $response->assertStatus(200)
            ->assertJsonFragment(['email' => $user->email]);
    }

    public function test_can_update_user()
    {
        $user = User::factory()->create();
        $headers = $this->authenticate();

        $data = [
            'name' => 'Atualizado',
            'lastname' => 'Silva',
            'phone' => '999999999',
            'email' => $this->faker->unique()->safeEmail,
            'password' => 'novaSenha123',
        ];

        Cache::shouldReceive('store')->once()->with('redis')->andReturnSelf();
        Cache::shouldReceive('forget')->once()->with('users:all');

        $response = $this->putJson("/api/user/{$user->id}", $data, $headers);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Usuário atualizado com sucesso']);
    }

    public function test_can_delete_user()
    {
        $user = User::factory()->create();
        $headers = $this->authenticate();

        Cache::shouldReceive('store')->once()->with('redis')->andReturnSelf();
        Cache::shouldReceive('forget')->once()->with('users:all');

        $response = $this->deleteJson("/api/user/{$user->id}", [], $headers);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Usuário excluído com sucesso']);
    }
    /* 
    public function test_user_list_cache_is_used()
    {
        Cache::shouldReceive('store')
            ->once()
            ->with('redis')
            ->andReturnSelf();

        Cache::shouldReceive('remember')
            ->once()
            ->with('users:all', \Mockery::type('DateTime'), \Closure::class)
            ->andReturn(collect([]));

        $headers = $this->authenticate();

        $response = $this->getJson('/api/user', $headers);

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'message', 'data']);
    }

    public function test_cache_is_invalidated_on_user_create()
    {
        $headers = $this->authenticate();

        $data = [
            'name' => 'João',
            'lastname' => 'Silva',
            'phone' => '111111111',
            'email' => $this->faker->unique()->safeEmail,
            'password' => 'senha123',
        ];

        Cache::shouldReceive('store')->once()->with('redis')->andReturnSelf();
        Cache::shouldReceive('forget')->once()->with('users:all');

        $response = $this->postJson('/api/user', $data, $headers);

        $response->assertStatus(201)
            ->assertJsonFragment(['message' => 'Usuário criado com sucesso']);
    } */
}
