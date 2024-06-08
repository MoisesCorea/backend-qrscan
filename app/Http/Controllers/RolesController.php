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
        return response()->json($roles);
    }

    public function show($id){
    

    $rol = Roles::find($id);

    if (!$rol) {
        return response()->json(['message' => 'El rol no existe.',
        'statusCode' => 404,], 404);
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
            return response()->json([
                'message' => 'Errores de validación',
                'statusCode' => 422,
                'messageDetail' => $validator->errors()->all()
            ], 422);
        }

        $rol = Roles::create([
            'name' => $request->name,
            'description'=> $request->description
        ]);

        return response()->json(['message'=> 'Rol creado correctamente', 'data'=>$rol], 201);
    }

    public function update(Request $request, $id)
    {

        $response = ["status" => 404, "msg" => ""];

        $rol = Roles::find($id);

        if (!$rol) {
            return response()->json(['message' => 'El rol no existe.',
            'statusCode' => 404,], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:roles,name,' . $id,
            'description'=> 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación',
                'statusCode' => 422,
                'messageDetail' => $validator->errors()->all()
            ], 422);
        }

        $rol->name = $request->name;
        $rol->description =$request->description;
        $rol->save();

        return response()->json(['message'=> 'Rol actualizado correctamente', 'data'=>$rol], 201);
    }

    public function destroy($id)
    {
        $rol = Roles::find($id);

        if (!$rol) {
            return response()->json(['message' => 'El rol no existe.',
            'statusCode' => 404,], 404);
        }
        
        $rowsAffected = Roles::destroy($id);

        $response = [
            "statusCode" => 200,
            "message" => "Rol eliminado exitosamente",
            "affected" => $rowsAffected
        ];

        return response()->json($response, 200);
    }

    
}
