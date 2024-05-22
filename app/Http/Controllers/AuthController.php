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

                return response()->json(['user_id' => $user_id, "access_token" =>$token->plainTextToken, "rol"=> $rol_name], 200);
            } else {
                
                return response()->json(['message' => 'Las credenciales de inicio de sesión no son válidas'], 401);
            }
        } else {
            return response()->json(['message' => 'Las credenciales de inicio de sesión no son válidas'], 401);
        }
}

 public function logout() {

    auth()->user()->tokens()->delete();

    return  ['Message'=> "Cierre de sesion exitoso"];
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
            return response()->json($validator->errors());
        }
    
    
        $user = Admins::create([
            'name' => $request->name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'alias' => $request->alias,
            'password' => Hash::make($request->password),
            'rol_id' => $request->rol_id 
        ]); 
    
    
    return response()->json(['data'=>$user]);
    
    }

    public function update(Request $request, $id)
    {

        $response = ["status" => 404, "msg" => ""];

        $user = Admins::find($id);

        if (!$user) {
            $response['msg'] = "El usuario no existe";
            return response()->json($response);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' =>'required|string|max:255',
            'alias' =>'required|string|max:50',
            'password' => 'required|string|min:8',
            'rol_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $user->name = $request->name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->alias = $request->alias;
        $user->password = Hash::make($request->password);
        $user->rol_id = $request->rol_id;

        $user->save();

        return response()->json($user);
    }

    public function index()
    {

        $users = Admins::all();

        // Retornar una vista o una respuesta JSON con los roles
        return response()->json($users);
    }

    public function show($id){
        $response = ["status" => 404, "msg" => ""];
    
        $user = Admins::find($id);
    
        if (!$user) {
            $response['msg'] = "El usuario no existe";
            return response()->json($response);
        }
        return response()->json($user);
    }

    public function destroy($id)
{
    $response = ["status" => 404, "msg" => ""];
    $user = Admins::find($id);

    if (!$user) {
        $response['msg'] = "El usuario no existe";
        return response()->json($response);
    }

    $rowsAffected = Admins::destroy($id);;

    $response = [
        "status" => 200,
        "msg" => "Usuario eliminado exitosamente",
        "affected" => $rowsAffected
    ];

    return response()->json($response);
}
}
