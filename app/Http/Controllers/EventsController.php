<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Events;
use Illuminate\Support\Facades\Validator; 

class EventsController extends Controller
{
    public function index()
    {
        $events = Events::all();
        return response()->json($events);
    }

    public function show($id){
        $response = ["status" => 404, "msg" => ""];
    
        $event =  Events::find($id);
    
        if (!$event) {
            $response['msg'] = "El evento no existe";
            return response()->json($response);
        }
    
        return response()->json($event);
        }

        public function store(Request $request)
        {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'change_attendance' => 'required|max:5',
                'description' => 'required|string|max:255',
            ]);
    
            if ($validator->fails()) {
                return response()->json($validator->errors());
            }
    
            $event = Events::create([
                'name' => $request->name,
                'change_attendance'=> $request->change_attendance,
                'description'  => $request->description,
            ]);
    
            return response()->json($event, 201);
        }

        public function update(Request $request, $id)
    {

        $response = ["status" => 404, "msg" => ""];

        $event = Events::find($id);

        if (!$event) {
            $response['msg'] = "El evento no existe";
            return response()->json($response);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'change_attendance' => 'required|max:5',
            'description' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $event->name = $request->name;
        $event->change_attendance  = $request->change_attendance;
        $event->description  = $request->description;
        $event->save();

        return response()->json($event);
    }

    public function updateStatus(Request $request, $id)
    {
        $event = Events::findOrFail($id);
    
        
        if (!$event) {
            return response()->json(['msg' => 'El evento no existe', 'status' => 404]);
        }
   
        $validator = Validator::make($request->all(), [
            'status' => 'required|boolean',
        ]);
    
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422); // Use appropriate HTTP status code for validation errors
        }
    
        // Update event status
        $event->status = $request->get('status');
    
        // Handle potential race condition for making only one event active
        if ($event->status && Events::where('status', true)->exists()) {
            // Fetch the currently active event (if any)
            $activeEvent = Events::where('status', true)->first();
    
            if ($activeEvent) {
                $activeEvent->status = false;
                $activeEvent->save();
            }
        }
    
        $event->save();
    
        return response()->json($event);
    }
    

    public function updateDailyAttendance(Request $request, $id)
    {
        $event = Events::findOrFail($id);
    
        
        if (!$event) {
            return response()->json(['msg' => 'El evento no existe', 'status' => 404]);
        }
   
        $validator = Validator::make($request->all(), [
            'daily_attendance' => 'required|boolean',
        ]);
    
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422); 
        }
    
       
        $event->daily_attendance = $request->get('daily_attendance');

    
        $event->save();
    
        return response()->json($event);
    }

    
    public function destroy($id)
    {
        $event  = Events::findOrFail($id);

        if (!$event ) {
            return response()->json(["msg"=>"El evento no existe", "status"=>404]);
        }
        
        $rowsAffected = Events::destroy($id);;

        return response()->json(["affected"=>$rowsAffected, "msg"=>"Registro eliminado correctamente", "status"=> 200]);
    }




}
