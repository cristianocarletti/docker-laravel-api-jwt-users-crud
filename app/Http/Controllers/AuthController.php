<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Http\Middleware\Authenticate as JWTMiddleware;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware(JWTMiddleware::class, ['except' => ['login', 'register']]);
    }

    /**
     * Login do usuário
     *
     * Autentica o usuário e retorna o token JWT.
     *
     * @unauthenticated
     * @bodyParam email string required E-mail do usuário. Exemplo: maria@example.com
     * @bodyParam password string required Senha do usuário. Exemplo: senha123
     * @response 200 {"status":"success","user":{},"auth":{"token":"...","type":"bearer"}}
     * @response 401 {"status":"error","message":"Não autorizado"}
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');
        $token = JWTAuth::attempt($credentials);

        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Não autorizado',
            ], 401);
        }

        $user = JWTAuth::user();

        return response()->json([
            'status' => 'success',
            'user' => $user,
            'auth' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
    }

    /**
     * Registro de usuário
     *
     * Registra o usuário e retorna o token JWT.
     *
     * @unauthenticated
     * @bodyParam name string required Nome do usuário. Exemplo: Maria
     * @bodyParam lastname string required Sobrenome. Exemplo: Silva
     * @bodyParam phone string required Telefone. Exemplo: +55 (11) 94321-6788
     * @bodyParam email string required E-mail. Exemplo: maria@example.com
     * @bodyParam password string required Senha. Exemplo: senha123
     * @response 200 {"status":"success","message":"Usuário criado com sucesso","user":{},"auth":{"token":"...","type":"bearer"}}
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'lastname' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'email' => 'required|string|email|max:150|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'lastname' => $request->lastname,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'status' => 'success',
            'message' => 'Usuário criado com sucesso',
            'user' => $user,
            'auth' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
    }

    /**
     * Logout do usuário
     *
     * Invalida o token JWT e limpa o cache.
     *
     * @authenticated
     * @response 200 {"status":"success","message":"Logout realizado com sucesso."}
     * @response 500 {"status":"error","message":"Erro ao realizar logout."}
     */
    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());

            // Invalida o token JWT
            JWTAuth::invalidate(JWTAuth::getToken());

            // Limpa todo o cache Redis
            Cache::flush(); // limpa todas as chaves, cuidado em produção

            return response()->json([
                'status' => 'success',
                'message' => 'Logout realizado com sucesso.',
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao realizar logout.',
            ], 500);
        }
    }

    /**
     * Refresh do token JWT
     *
     * Retorna novo token e dados do usuário.
     *
     * @authenticated
     * @response 200 {"status":"success","user":{},"auth":{"token":"...","type":"bearer"}}
     */
    public function refresh()
    {
        return response()->json([
            'status' => 'success',
            'user' => JWTAuth::user(),
            'auth' => [
                'token' => JWTAuth::refresh(),
                'type' => 'bearer',
            ]
        ]);
    }
}
