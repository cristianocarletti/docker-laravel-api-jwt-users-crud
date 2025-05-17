<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Http\Middleware\Authenticate as JWTMiddleware;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(JWTMiddleware::class);
    }

    /**
     * @OA\Post(
     *     path="/api/user/index",
     *     summary="Lista de usuários",
     *     tags={"Usuários"},
     *     description="Lista usuários com filtros opcionais de nome e e-mail",
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Maria"),
     *             @OA\Property(property="email", type="string", example="maria@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de usuários",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example=null),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Maria"),
     *                 @OA\Property(property="lastname", type="string", example="Silva"),
     *                 @OA\Property(property="phone", type="string", example="+55 (11) 94321-6788"),
     *                 @OA\Property(property="email", type="string", example="maria@example.com")
     *             ))
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            $users = Cache::store('redis')->remember('users:all', now()->addMinutes(10), function () use ($request) {
                $usersQuery = User::query();
                if ($request->has('name')) {
                    $usersQuery->where('name', 'like', '%' . $request->name . '%');
                }
                if ($request->has('email')) {
                    $usersQuery->where('email', 'like', '%' . $request->email . '%');
                }
                return $usersQuery->get();
            });

            return ResponseHelper::success($users);
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token inválido ou ausente',
            ], 401);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/user",
     *     summary="Registro de usuário",
     *     tags={"Usuários"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "lastname", "phone", "email", "password"},
     *             @OA\Property(property="name", type="string", example="Maria"),
     *             @OA\Property(property="lastname", type="string", example="Silva"),
     *             @OA\Property(property="phone", type="string", example="+55 (11) 94321-6788"),
     *             @OA\Property(property="email", type="string", example="maria@example.com"),
     *             @OA\Property(property="password", type="string", example="senha123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Usuário criado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Usuário criado com sucesso"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Maria"),
     *                 @OA\Property(property="lastname", type="string", example="Silva"),
     *                 @OA\Property(property="phone", type="string", example="+55 (11) 94321-6788"),
     *                 @OA\Property(property="email", type="string", example="maria@example.com")
     *             )
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
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

            Cache::store('redis')->forget('users:all');

            return ResponseHelper::success($user, 'Usuário criado com sucesso', 201);
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token inválido ou ausente',
            ], 401);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/user/{id}",
     *     summary="Exibe um usuário",
     *     tags={"Usuários"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Usuário retornado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example=null),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Maria"),
     *                 @OA\Property(property="email", type="string", example="maria@example.com")
     *             )
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        try {
            $user = User::find($id);
            return ResponseHelper::success($user);
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token inválido ou ausente',
            ], 401);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/user/{id}",
     *     summary="Atualiza um usuário",
     *     tags={"Usuários"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="João"),
     *             @OA\Property(property="lastname", type="string", example="Pereira"),
     *             @OA\Property(property="phone", type="string", example="(11) 91234-5678"),
     *             @OA\Property(property="email", type="string", example="joao@example.com"),
     *             @OA\Property(property="password", type="string", example="novaSenha123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuário atualizado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Usuário atualizado com sucesso"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="João"),
     *                 @OA\Property(property="email", type="string", example="joao@example.com")
     *             )
     *         )
     *     )
     * )
     */
    public function update(Request $request, User $user)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:100',
                'lastname' => 'required|string|max:100',
                'phone' => 'required|string|max:20',
                'email' => ['required', 'email', 'max:150', Rule::unique('users')->ignore($user->id)],
                'password' => 'nullable|string|min:6',
            ]);

            $user->update([
                'name' => $request->name,
                'lastname' => $request->lastname,
                'phone' => $request->phone,
                'email' => $request->email,
                'password' => $request->password ? Hash::make($request->password) : $user->password,
            ]);

            Cache::store('redis')->forget('users:all');

            return ResponseHelper::success($user, 'Usuário atualizado com sucesso', 200);
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token inválido ou ausente',
            ], 401);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/user/{id}",
     *     summary="Remove um usuário",
     *     tags={"Usuários"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Usuário removido com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Usuário excluído com sucesso"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="João"),
     *                 @OA\Property(property="email", type="string", example="joao@example.com")
     *             )
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        try {
            $user = User::find($id);
            $user->delete();

            Cache::store('redis')->forget('users:all');

            return ResponseHelper::success($user, 'Usuário excluído com sucesso', 200);
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token inválido ou ausente',
            ], 401);
        }
    }
}
