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
    
        $event =  Events::find($id);
    
        if (!$event) {
            return response()->json(['message' => 'El evento no existe.',
            'statusCode' => 404,], 404);
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
                return response()->json([
                    'message' => 'Errores de validación',
                    'statusCode' => 422,
                    'messageDetail' => $validator->errors()->all()
                ], 422);
            }
    
            $event = Events::create([
                'name' => $request->name,
                'change_attendance'=> $request->change_attendance,
                'description'  => $request->description,
            ]);
    
            return response()->json(['message'=>'Evento creado correctamente', 'data'=>$event], 201);
        }

        public function update(Request $request, $id)
    {


        $event = Events::find($id);

        if (!$event) {
            return response()->json(['message' => 'El evento no existe.',
            'statusCode' => 404,], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'change_attendance' => 'required|max:5',
            'description' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación',
                'statusCode' => 422,
                'messageDetail' => $validator->errors()->all()
            ], 422);
        }

        $event->name = $request->name;
        $event->change_attendance  = $request->change_attendance;
        $event->description  = $request->description;
        $event->save();

        return response()->json(['message'=>'Evento actualizado correctamente', 'data'=>$event], 201);
    }

    public function updateStatus(Request $request, $id)
    {
        $event = Events::find($id);
    
        
        if (!$event) {
            return response()->json(['message' => 'El evento no existe', 'statusCode' => 404], 404);
        }
   
        $validator = Validator::make($request->all(), [
            'status' => 'required|boolean',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación',
                'statusCode' => 422,
                'messageDetail' => $validator->errors()->all()
            ], 422);
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
    
        return response()->json(['message'=> 'Estado actualizado', 'data'=>$event], 201 );
    }
    

    public function updateDailyAttendance(Request $request, $id)
    {
        $event = Events::find($id);
    
        
        if (!$event) {
            return response()->json(['message' => 'El evento no existe', 'statusCode' => 404], 404);
        }
   
        $validator = Validator::make($request->all(), [
            'daily_attendance' => 'required|boolean',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación',
                'statusCode' => 422,
                'messageDetail' => $validator->errors()->all()
            ], 422);
        }
    
       
        $event->daily_attendance = $request->get('daily_attendance');

    
        $event->save();
    
        return response()->json(['message'=> 'Asistencia diaria actualizada', 'data'=>$event], 201 );
    }

    
    public function destroy($id)
    {
        $event  = Events::find($id);

        if (!$event ) {
            return response()->json(["message"=>"El evento no existe", "statusCode"=>404], 404);
        }
        
        $rowsAffected = Events::destroy($id);

        $response = [
            "statusCode" => 200,
            "message" => "Evento eliminado exitosamente",
            "affected" => $rowsAffected
        ];

        return response()->json($response, 200);
    }




}
