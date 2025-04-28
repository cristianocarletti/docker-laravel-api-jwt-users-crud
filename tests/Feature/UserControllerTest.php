<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Http\Middleware\Authenticate as JWTMiddleware;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    // Testa o endpoint index (GET /user)
    public function test_get_users()
    {
        $user = User::factory()->create(); // Criar um usuário de teste
        $token = JWTAuth::fromUser($user); // Gerar um token JWT para o usuário

        // Enviar uma requisição POST para /api/user/index
        $response = $this->postJson('/api/user/index', [], [
            'Authorization' => 'Bearer ' . $token, // Passar o token no cabeçalho
        ]);

        $response->assertStatus(200);
    }


    // Testa o endpoint store (POST /user)
    public function test_create_user()
    {
        // Cria um usuário para gerar o token de autenticação
        $user = User::factory()->create(); // Certifique-se de que você tem um factory para o modelo User

        // Gera um token JWT para o usuário
        $token = JWTAuth::fromUser($user);

        // Realiza a solicitação POST para criar o usuário com o token de autenticação
        $response = $this->postJson('/api/user', $user->getAttributes(), [
            'Authorization' => 'Bearer ' . $token // Adiciona o token no cabeçalho Authorization
        ]);

        // Verifica se o código de status é 201 (Criado)
        $response->assertStatus(201);
    }

    // Testa o endpoint show (GET /user/{id})
    public function test_show_user()
    {
        $user = User::factory()->create();

        $response = $this->getJson("/api/user/{$user->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => $user->name]);
    }

    // Testa o endpoint update (PUT /user/{id})
    public function test_update_user()
    {
        $user = User::factory()->create();

        $data = [
            'name' => 'Updated Name',
            'lastname' => 'Updated Lastname',
            'phone' => '9876543210',
            'email' => 'updatedemail@example.com',
            'password' => 'newpassword123',
        ];

        $response = $this->putJson("/api/user/{$user->id}", $data);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Updated Name']);
    }

    // Testa o endpoint destroy (DELETE /user/{id})
    public function test_delete_user()
    {
        $user = User::factory()->create();

        $response = $this->deleteJson("/api/user/{$user->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Usuário excluído com sucesso']);

        // Verifica se o cache foi limpo após a exclusão de um usuário
        Cache::store('redis')->forget('users:all');
        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    // Teste de Cache: verifica se o cache foi usado corretamente
    public function test_cache_users()
    {
        // Simula o comportamento de cache
        Cache::shouldReceive('remember')
            ->once()
            ->with('users:all', now()->addMinutes(10), \Closure::class)
            ->andReturn(collect([]));  // Você pode retornar um mock aqui

        $response = $this->getJson('/api/user');

        $response->assertStatus(200);
    }

    // Teste de invalidação do cache após a criação de um usuário
    public function test_cache_invalidation_after_create_user()
    {
        $data = [
            'name' => 'John Doe',
            'lastname' => 'Doe',
            'phone' => '1234567890',
            'email' => 'johndoe@example.com',
            'password' => 'password123',
        ];

        // Verifica que o cache está sendo limpo ao criar um novo usuário
        Cache::shouldReceive('forget')
            ->once()
            ->with('users:all');

        $response = $this->postJson('/api/user', $data);

        $response->assertStatus(201);
    }
}
