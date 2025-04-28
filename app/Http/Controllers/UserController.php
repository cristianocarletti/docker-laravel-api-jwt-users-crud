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

    public function update(Request $request, User $user)
    {
        try {
            /* if ($user->id !== auth()->user()->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Você não tem permissão para atualizar este usuário',
                ], 403); // 403 Forbidden
            } */

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
