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
     * Lista de usuários
     *
     * Retorna lista de usuários com filtros opcionais por nome ou e-mail.
     *
     * @authenticated
     * @queryParam name string Nome para filtro. Exemplo: Maria
     * @queryParam email string E-mail para filtro. Exemplo: maria@example.com
     * @response 200 {"status":"success","message":null,"data":[]}
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
     * Criação de usuário
     *
     * Cria um novo usuário no sistema.
     *
     * @authenticated
     * @bodyParam name string required Nome do usuário. Exemplo: Maria
     * @bodyParam lastname string required Sobrenome. Exemplo: Silva
     * @bodyParam phone string required Telefone. Exemplo: +55 (11) 94321-6788
     * @bodyParam email string required E-mail. Exemplo: maria@example.com
     * @bodyParam password string required Senha. Exemplo: senha123
     * @response 201 {"status":"success","message":"Usuário criado com sucesso","data":{}}
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
     * Exibe um usuário
     *
     * Retorna os dados de um usuário específico.
     *
     * @authenticated
     * @urlParam id integer required ID do usuário. Exemplo: 1
     * @response 200 {"status":"success","message":null,"data":{}}
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
     * Atualiza um usuário
     *
     * Atualiza os dados de um usuário existente.
     *
     * @authenticated
     * @urlParam id integer required ID do usuário. Exemplo: 1
     * @bodyParam name string required Nome. Exemplo: João
     * @bodyParam lastname string required Sobrenome. Exemplo: Pereira
     * @bodyParam phone string required Telefone. Exemplo: (11) 91234-5678
     * @bodyParam email string required E-mail. Exemplo: joao@example.com
     * @bodyParam password string Senha nova. Exemplo: novaSenha123
     * @response 200 {"status":"success","message":"Usuário atualizado com sucesso","data":{}}
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
     * Remove um usuário
     *
     * Deleta um usuário do sistema.
     *
     * @authenticated
     * @urlParam id integer required ID do usuário. Exemplo: 1
     * @response 200 {"status":"success","message":"Usuário excluído com sucesso","data":{}}
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
