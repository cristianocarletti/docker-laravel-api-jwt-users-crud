<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Http\Middleware\Authenticate as JWTMiddleware;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware(JWTMiddleware::class, ['except' => ['login', 'register']]);
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Login de usuário",
     *     tags={"Autenticação"},
     *     description="Autentica o usuário e retorna um token JWT",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", example="maria.silva8@example.com"),
     *             @OA\Property(property="password", type="string", example="senha123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login realizado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="user", type="object"),
     *             @OA\Property(property="auth", type="object",
     *                 @OA\Property(property="token", type="string", example="jwt_token_aqui"),
     *                 @OA\Property(property="type", type="string", example="bearer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Credenciais inválidas",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Não autorizado")
     *         )
     *     )
     * )
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
     * @OA\Post(
     *     path="/api/register",
     *     summary="Registro de usuário",
     *     tags={"Autenticação"},
     *     description="Registra o usuário e retorna um token JWT",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "lastname", "phone", "email", "password"},
     *             @OA\Property(property="name", type="string", example="Maria"),
     *             @OA\Property(property="lastname", type="string", example="Silva"),
     *             @OA\Property(property="phone", type="string", example="+55 (11) 94321-6788"),
     *             @OA\Property(property="email", type="string", example="maria.silva8@example.com"),
     *             @OA\Property(property="password", type="string", example="senha123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuário registrado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Usuário criado com sucesso"),
     *             @OA\Property(property="user", type="object"),
     *             @OA\Property(property="auth", type="object",
     *                 @OA\Property(property="token", type="string", example="jwt_token_aqui"),
     *                 @OA\Property(property="type", type="string", example="bearer")
     *             )
     *         )
     *     )
     * )
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

    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());

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
