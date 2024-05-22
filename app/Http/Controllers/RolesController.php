<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Roles;
use Illuminate\Support\Facades\Validator; 

class RolesController extends Controller
{
    public function index()
    {

        $roles = Roles::all();

        // Retornar una vista o una respuesta JSON con los roles
        return response()->json($roles);
    }

    public function show($id){
    $response = ["status" => 404, "msg" => ""];

    $rol = Roles::find($id);

    if (!$rol) {
        $response['msg'] = "El rol no existe";
        return response()->json($response);
    }

    return response()->json($rol);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:roles',
            'description'=> 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $rol = Roles::create([
            'name' => $request->name,
            'description'=> $request->description
        ]);

        return response()->json($rol, 201);
    }

    public function update(Request $request, $id)
    {

        $response = ["status" => 404, "msg" => ""];

        $rol = Roles::findOrFail($id);

        if (!$rol) {
            $response['msg'] = "El rol no existe";
            return response()->json($response);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description'=> 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $rol->name = $request->name;
        $rol->description =$request->description;
        $rol->save();

        return response()->json($rol);
    }

    public function destroy($id)
    {
        $rol = Roles::findOrFail($id);

        if (!$rol) {
            $response['msg'] = "El rol no existe";
            return response()->json(["msg"=>"El rol no existe", "status"=>404]);
        }
        
        $rowsAffected = Roles::destroy($id);;

        return response()->json(["affected"=>$rowsAffected, "msg"=>"Registro eliminado correctamente", "status"=> 200]);
    }

    
}
