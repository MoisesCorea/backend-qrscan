<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator; 
use App\Models\Departments;

class DepartmentsController extends Controller
{
    public function index()
    {

        $departments = Departments::all();

        return response()->json($departments);
    }

    public function show($id){
        $department = Departments::find($id);

        if (!$department) {
            return response()->json(['message' => 'El departamento no existe.',
            'statusCode' => 404,], 404);
        }

        return response()->json($department);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:departments',
            'description'=> 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación',
                'statusCode' => 422,
                'messageDetail' => $validator->errors()->all()
            ], 422);
        }

        $department = Departments::create([
            'name' => $request->name,
            'description'=> $request->description
        ]);

        return response()->json(['message'=> 'Departamento creado correctamente', 'data'=>$department], 201);
    }

    public function update(Request $request, $id)
    {

        $department = Departments::find($id);

        if (!$department) {
            return response()->json(['message' => 'El departamento no existe.',
            'statusCode' => 404,], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:departments,name,'. $id,
            'description'=> 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación',
                'statusCode' => 422,
                'messageDetail' => $validator->errors()->all()
            ], 422);
        }

        $department->name = $request->name;
        $department->description =$request->description;
        $department->save();

        return response()->json(['message'=> 'Departamento actualizado correctamente', 'data'=>$department], 200);
    }

    public function destroy($id)
    {
        $department  = Departments::find($id);

        if (!$department ) {
            return response()->json(['message' => 'El departamento no existe.',
            'statusCode' => 404,], 404);
        }
        
        $rowsAffected = Departments::destroy($id);


        $response = [
            "statusCode" => 200,
            "message" => "Departamento eliminado exitosamente",
            "affected" => $rowsAffected
        ];

        return response()->json($response, 200);
    }

}
