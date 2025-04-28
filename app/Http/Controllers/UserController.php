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
        $this->middleware(JWTMiddleware::class, ['except' => ['login', 'register']]);
    }
    /**
     * @OA\Post(
     *     path="/api/user/index",
     *     summary="Lista de usuários",
     *     description="Lista de usuários",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *              @OA\Property(property="name", type="string", example="Maria"),
     *              @OA\Property(property="email", type="string", example="maria.silva8@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Solicitação bem-sucedida",
     *          @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Maria"),
     *             @OA\Property(property="lastname", type="string", example="Silva"),
     *             @OA\Property(property="phone", type="string", example="+55 (11) 94321-6788"),
     *             @OA\Property(property="email", type="string", example="maria.silva8@example.com"),
     *         )
     *     ),
     * )
     */
    public function index(Request $request)
    {
        try {
            // Gerar uma chave única para o cache baseado nos filtros fornecidos
            //$cacheKey = 'users:' . md5(json_encode($request->all()));

            // Verificar se os dados estão no cache
            $users = Cache::store('redis')->remember('users:all', now()->addMinutes(10), function () use ($request) {
                // Inicializa a query base
                $usersQuery = User::query();

                // Filtra por nome, se o parâmetro 'name' estiver presente
                if ($request->has('name')) {
                    $usersQuery->where('name', 'like', '%' . $request->name . '%');
                }

                // Filtra por email, se o parâmetro 'email' estiver presente
                if ($request->has('email')) {
                    $usersQuery->where('email', 'like', '%' . $request->email . '%');
                }

                // Recupera os usuários filtrados ou todos se não houver filtros
                return $usersQuery->get();
            });

            return ResponseHelper::success($users);
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token inválido ou ausente',
            ], 401); // 401 Unauthorized
        }
    }
    /**
     * @OA\Post(
     *     path="/api/user",
     *     summary="Registro de usuário",
     *     description="Registra o usuário e retorna um token JWT",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *              @OA\Property(property="name", type="string", example="Maria"),
     *              @OA\Property(property="lastname", type="string", example="Silva"),
     *              @OA\Property(property="phone", type="string", example="+55 (11) 94321-6788"),
     *              @OA\Property(property="email", type="string", example="maria.silva8@example.com"),
     *              @OA\Property(property="password", type="string", example="senha123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuário criado com sucesso",
     *          @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Maria"),
     *             @OA\Property(property="lastname", type="string", example="Silva"),
     *             @OA\Property(property="phone", type="string", example="+55 (11) 94321-6788"),
     *             @OA\Property(property="email", type="string", example="maria.silva8@example.com"),
     *         )
     *     ),
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

            // Criação do usuário
            $user = User::create([
                'name' => $request->name,
                'lastname' => $request->lastname,
                'phone' => $request->phone,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ]);

            // Invalidar o cache relacionado a usuários
            Cache::store('redis')->forget('users:all');

            return ResponseHelper::success($user, 'Usuário criado com sucesso', 201);
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token inválido ou ausente',
            ], 401); // 401 Unauthorized
        }
    }
    /**
     * @OA\Get(
     *     path="/api/user/{1}",
     *     summary="Exibe dados de usuário",
     *     description="Exibe dados de usuário",
     *     @OA\Response(
     *         response=200,
     *         description="Solicitação bem-sucedida",
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Maria"),
     *             @OA\Property(property="lastname", type="string", example="Silva"),
     *             @OA\Property(property="phone", type="string", example="+55 (11) 94321-6788"),
     *             @OA\Property(property="email", type="string", example="maria.silva8@example.com"),
     *         )
     *     ),
     * )
     */
    public function show($id)
    {
        try {
            // Recupera um único usuário, sem cache, pois geralmente são consultas específicas
            $user = User::find($id);

            return ResponseHelper::success($user);
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token inválido ou ausente',
            ], 401); // 401 Unauthorized
        }
    }
    /**
     * @OA\Put(
     *     path="/api/user/{1}",
     *     summary="Atualiza dados de usuário",
     *     description="Atualiza dados de usuário",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *              @OA\Property(property="name", type="string", example="Maria"),
     *              @OA\Property(property="lastname", type="string", example="Silva"),
     *              @OA\Property(property="phone", type="string", example="+55 (11) 94321-6788"),
     *              @OA\Property(property="email", type="string", example="maria.silva8@example.com"),
     *              @OA\Property(property="password", type="string", example="senha123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuário atualizado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Maria"),
     *             @OA\Property(property="lastname", type="string", example="Silva"),
     *             @OA\Property(property="phone", type="string", example="+55 (11) 94321-6788"),
     *             @OA\Property(property="email", type="string", example="maria.silva8@example.com"),
     *         )
     *     ),
     * )
     */
    public function update(Request $request, User $user)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:100',
                'lastname' => 'required|string|max:100',
                'phone' => 'required|string|max:20',
                'email' => [
                    'required',
                    'email',
                    'max:150',
                    Rule::unique('users')->ignore($user->id),
                ],
                'password' => 'nullable|string|min:6',
            ]);

            // Atualização do usuário
            $user->update([
                'name' => $request->name,
                'lastname' => $request->lastname,
                'phone' => $request->phone,
                'email' => $request->email,
                'password' => $request->password ? Hash::make($request->password) : $user->password,
            ]);

            // Invalidar o cache relacionado aos usuários
            Cache::store('redis')->forget('users:all');

            return ResponseHelper::success($user, 'Usuário atualizado com sucesso', 200);
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token inválido ou ausente',
            ], 401); // 401 Unauthorized
        }
    }
    /**
     * @OA\Delete(
     *     path="/api/user/{1}",
     *     summary="Exclui um usuário",
     *     description="Exclui um usuário",
     *     @OA\Response(
     *         response=200,
     *         description="Usuário excluído com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Maria"),
     *             @OA\Property(property="lastname", type="string", example="Silva"),
     *             @OA\Property(property="phone", type="string", example="+55 (11) 94321-6788"),
     *             @OA\Property(property="email", type="string", example="maria.silva8@example.com"),
     *         )
     *     ),
     * )
     */
    public function destroy($id)
    {
        try {
            $user = User::find($id);
            $user->delete();

            // Invalidar o cache relacionado aos usuários
            Cache::store('redis')->forget('users:all');

            return ResponseHelper::success($user, 'Usuário excluído com sucesso', 200);
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token inválido ou ausente',
            ], 401); // 401 Unauthorized
        }
    }
}
