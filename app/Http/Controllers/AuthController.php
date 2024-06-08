<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Admins; 
use App\Models\Roles; 
use Illuminate\Support\Facades\Validator; 
use Illuminate\Support\Facades\Auth;





class AuthController extends Controller
{
    public function login(Request $request)
{
        $data = json_decode($request->getContent());
        $user = Admins::where('email', $data->email)->first();
    
        if ($user) {
            if (Hash::check($data->password, $user->password)) {
                $token = $user->createToken("auth_token");
                $user_id = $user-> id;
                $rol_id = $user-> rol_id;
                
                $rol = Roles::find($rol_id); // Buscar el rol por ID
                if ($rol) {
                    $rol_name = $rol->name; // Obtener el nombre del rol
                } else {
                    $rol_name = null; // o un valor de error predeterminado
                }

                return response()->json(['message' => 'Inicio de sesión exitoso', 'statusCode'=> 200, 'user_id' => $user_id, "access_token" =>$token->plainTextToken, "rol"=> $rol_name], 200);
            } else {
                
                return response()->json(['message' => 'Las credenciales de inicio de sesión no son válidas', 'statusCode'=> 401], 401);
            }
        } else {
            return response()->json(['message' => 'Las credenciales de inicio de sesión no son válidas', 'statusCode'=> 401], 401);
        }
}

 public function logout() {

    auth()->user()->tokens()->delete();

    return  ['message'=> "Cierre de sesion exitoso", 'statusCode' => 200];
 }

    public function register (Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' =>'required|string|max:255|unique:admins|email',
            'alias' =>'required|string|max:50|unique:admins',
            'password' => 'required|string|min:8',
            'rol_id' => 'required',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
            'message' => 'Errores de validación',
            'statusCode' => 422,
            'messageDetail' => $validator->errors()->all()
        ], 422);
        }
    
    
        $user = Admins::create([
            'name' => $request->name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'alias' => $request->alias,
            'password' => Hash::make($request->password),
            'rol_id' => $request->rol_id 
        ]); 
    
    
    return response()->json(['message'=> 'Usuario creado correctamente', 'data'=>$user], 200);
    
    }

    public function update(Request $request, $id)
    {

        $response = ["status" => 404, "msg" => ""];

        $user = Admins::find($id);

        if (!$user) {
            $response['msg'] = "El usuario no existe";
            return response()->json($response);
        }

        $rules = [
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|max:255|unique:admins,email,' . $id,
            'alias' => 'required|string|max:50|unique:admins,alias,' . $id,
        ];

        if ($request->has('password') && $request->filled('password')) {
            $rules['password'] = 'required|string|min:8';
        }

        if ($request->has('rol_id')) {
            $rules['rol_id'] = 'required';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
            'message' => 'Errores de validación',
            'statusCode' => 422,
            'messageDetail' => $validator->errors()->all()
        ], 422);
        }
        

        $user->name = $request->name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->alias = $request->alias;
        if ($request->has('password') && $request->filled('password') ) {
            $user->password = Hash::make($request->password);
        }

        if ($request->has('rol_id')) {
            $user->rol_id = $request->rol_id;
        }
      
       

        $user->save();

        return response()->json(['message'=> 'Usuario actualizado correctamente', 'data'=>$user], 200);
    }

    public function index()
    {

        $users = Admins::all();

        // Retornar una vista o una respuesta JSON con los roles
        return response()->json($users);
    }

    public function show($id){
    
        $user = Admins::find($id);
    
        if (!$user) {
            return response()->json(['message' => 'El usuario no existe.',
        'statusCode' => 404,], 404);
        }
        return response()->json($user);
    }

    public function destroy($id)
{
    $user = Admins::find($id);

    if (!$user) {
        return response()->json(['message' => 'El usuario no existe.',
        'statusCode' => 404,], 404);
    }

    $rowsAffected = Admins::destroy($id);;

    $response = [
        "statusCode" => 200,
        "message" => "Usuario eliminado exitosamente",
        "affected" => $rowsAffected
    ];

    return response()->json($response, 200);
}

public function changePassword(Request $request)
    {
        // Validar la solicitud
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed', // El campo `new_password_confirmation` debe coincidir
        ]);

        if ($validator->fails()) {
            return response()->json([
            'message' => 'Errores de validación',
            'statusCode' => 422,
            'messageDetail' => $validator->errors()->all()
        ], 422);

    }

        $user = $request->user();

        // Verificar la contraseña actual
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'El password actual es incorrecto', 'statusCode'=> 401], 401);
        }

        // Actualizar la contraseña
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'message' => 'Password actualizado correctamente', 'statusCode'=> 200
        ]);
    }
}
