<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\Shifts;
use Illuminate\Support\Facades\Validator; 


class ShiftsController extends Controller
{
    public function index()
    {
        $shifts = Shifts::all();
        return response()->json($shifts);
    }

    public function show($id){
    
        $shift =  Shifts::find($id);
    
        if (!$shift) {
            return response()->json(['message' => 'El horario no existe.',
            'statusCode' => 404,], 404);
        }
    
        return response()->json($shift);
        }

        public function store(Request $request)
        {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:shifts,name',
                'entry_time' => 'required|date_format:H:i:s',
                'finish_time' => 'required|date_format:H:i:s',
                'shift_duration' => 'required|integer',
                'mothly_late_allowance' => 'required|integer',
                'days'=>'required'
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Errores de validación',
                    'statusCode' => 422,
                    'messageDetail' => $validator->errors()->all()
                ], 422);
            }
    
            $shift = Shifts::create([
                'name' => $request->name,
                'entry_time'  => $request->entry_time,
                'finish_time'  => $request->finish_time,
                'shift_duration'  => $request->shift_duration,
                'mothly_late_allowance'  => $request->mothly_late_allowance,
                'days' => json_encode($request->days)
            ]);
    
            return response()->json(['message'=> 'Horario creado correctamente', 'data'=>$shift, 201], 201);
        }

        public function update(Request $request, $id)
    {

        $shift = Shifts::find($id);

        if (!$shift) {
            return response()->json(['message' => 'El horario no existe.',
            'statusCode' => 404,], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:shifts,name,' . $id,
            'entry_time' => 'required|date_format:H:i:s',
            'finish_time' => 'required|date_format:H:i:s',
            'shift_duration' => 'required|integer',
            'mothly_late_allowance' => 'required|integer',
            'days' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación',
                'statusCode' => 422,
                'messageDetail' => $validator->errors()->all()
            ], 422);
        }

        $shift->name = $request->name;
        $shift->entry_time  = $request->entry_time;
        $shift->finish_time  = $request->finish_time;
        $shift->shift_duration  = $request->shift_duration;
        $shift->mothly_late_allowance  = $request->mothly_late_allowance;
        $shift->days = $request->days;
        $shift->save();

        return response()->json(['message'=> 'Horario actualizado correctamente', 'data'=>$shift, 201], 201);
    }


    public function destroy($id)
    {
        $shift  = Shifts::findOrFail($id);

        if (!$shift ) {
            return response()->json(['message' => 'El horario no existe.',
            'statusCode' => 404,], 404);
        }
        
        $rowsAffected = Shifts::destroy($id);

        $response = [
            "statusCode" => 200,
            "message" => "Rol eliminado exitosamente",
            "affected" => $rowsAffected
        ];



        return response()->json($response, 200);
    }

}
