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

        // Retornar una vista o una respuesta JSON con los roles
        return response()->json($departments);
    }

    public function show($id){
    $response = ["status" => 404, "msg" => ""];

    $department = Departments::find($id);

    if (!$department) {
        $response['msg'] = "El rol no existe";
        return response()->json($response);
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
            return response()->json($validator->errors());
        }

        $department = Departments::create([
            'name' => $request->name,
            'description'=> $request->description
        ]);

        return response()->json($department, 201);
    }

    public function update(Request $request, $id)
    {

        $response = ["status" => 404, "msg" => ""];

        $department = Departments::findOrFail($id);

        if (!$department) {
            $response['msg'] = "El departamento no existe";
            return response()->json($response);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description'=> 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $department->name = $request->name;
        $department->description =$request->description;
        $department->save();

        return response()->json($department);
    }

    public function destroy($id)
    {
        $department  = Departments::findOrFail($id);

        if (!$department ) {
            return response()->json(["msg"=>"El departamento no existe", "status"=>404]);
        }
        
        $rowsAffected = Departments::destroy($id);;

        return response()->json(["affected"=>$rowsAffected, "msg"=>"Registro eliminado correctamente", "status"=> 200]);
    }

}
