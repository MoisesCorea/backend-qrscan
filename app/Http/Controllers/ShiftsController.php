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
        $response = ["status" => 404, "msg" => ""];
    
        $shift =  Shifts::find($id);
    
        if (!$shift) {
            $response['msg'] = "El rol no existe";
            return response()->json($response);
        }
    
        return response()->json($shift);
        }

        public function store(Request $request)
        {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'entry_time' => 'required|date_format:H:i:s',
                'finish_time' => 'required|date_format:H:i:s',
                'shift_duration' => 'required|integer',
                'mothly_late_allowance' => 'required|integer'
            ]);
    
            if ($validator->fails()) {
                return response()->json($validator->errors());
            }
    
            $shift = Shifts::create([
                'name' => $request->name,
                'entry_time'  => $request->entry_time,
                'finish_time'  => $request->finish_time,
                'shift_duration'  => $request->shift_duration,
                'mothly_late_allowance'  => $request->mothly_late_allowance,
            ]);
    
            return response()->json($shift, 201);
        }

        public function update(Request $request, $id)
    {

        $response = ["status" => 404, "msg" => ""];

        $shift = Shifts::find($id);

        if (!$shift) {
            $response['msg'] = "El turno no existe";
            return response()->json($response);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'entry_time' => 'required|date_format:H:i:s',
            'finish_time' => 'required|date_format:H:i:s',
            'shift_duration' => 'required|integer',
            'mothly_late_allowance' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $shift->name = $request->name;
        $shift->entry_time  = $request->entry_time;
        $shift->finish_time  = $request->finish_time;
        $shift->shift_duration  = $request->shift_duration;
        $shift->mothly_late_allowance  = $request->mothly_late_allowance;
        $shift->save();

        return response()->json($shift);
    }


    public function destroy($id)
    {
        $shift  = Shifts::findOrFail($id);

        if (!shift ) {
            return response()->json(["msg"=>"El turno no existe", "status"=>404]);
        }
        
        $rowsAffected = Shifts::destroy($id);;

        return response()->json(["affected"=>$rowsAffected, "msg"=>"Registro eliminado correctamente", "status"=> 200]);
    }

}
