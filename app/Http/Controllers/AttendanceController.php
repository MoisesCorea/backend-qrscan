<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Users;
use App\Models\Events;
use App\Models\Departments;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator; 

class AttendanceController extends Controller

{
    //Registro de sistencia
    public function attachAttendance(Request $request, String $id)
    {
        $user = Users::find($id);

        if (!$user) {
            return response()->json(['message' => 'No existe el usuario', 'statusCode'=>404], 404);
        }

        if ($user->status === "Inactivo") {
            return response()->json(['message' => 'El usuario no puede registrar su entrada, se encuentra inactivo', 'statusCode'=>403], 403);
        }
    
        // Buscar el evento activo
        $active = Events::where('status', true)->first();
        if (!$active) {
            return response()->json(['message' => 'No hay eventos activos', 'statusCode'=>404], 404);
        }
        //$dailyAttendance = Events::where('daily_attendance', true)->first();
        $dailyAttendance = $active->daily_attendance;
            
      

       
    
        // Obtener la fecha actual y la hora actual
        $fechaActual = Carbon::now()->format('Y-m-d');
        $horaActual = Carbon::now()->format('H:i:s');
    
        $recentAttendance = $user->events()
                                            ->wherePivot('attendance_date', $fechaActual)
                                            ->orderBy('entry_time', 'desc')
                                            ->first();
    
    
        if ($dailyAttendance) { // Evento con asistencia diaria
            if ($recentAttendance) {

                 
                // Obtiene el entry_time y le suma 4 horas
                $entryTimePlus = Carbon::parse($recentAttendance->pivot->entry_time)->addMinutes($active->change_attendance);
                
                // Obtiene la hora actual
              
                $currentTime=Carbon::now();
                // Comprueba si entry_time + 4 horas es menor o igual que la hora actual
    
    
                if ( $entryTimePlus->lte($currentTime)) {
                    if (!$recentAttendance->pivot->finish_time) {
                        try {
                            // Finaliza la asistencia existente
                            $user->events()
                                 ->wherePivot('attendance_date', $fechaActual)
                                 ->updateExistingPivot($active->id, [
                                     'finish_time' => $horaActual,
                                 ]);
                          
                            return response()->json(['message' => 'Regisro de asistencia finalizado con éxito', 'statusCode'=>200], 200);
                        } catch (Exception $e) {
                            return response()->json(['message' => 'Error al finalizar la asistencia: ' . $e->getMessage(),  'statusCode'=>400], 400);
                        }
                    } else {
                        return response()->json(['message' => 'Ya ha registrado su salida', 'statusCode'=>409], 409);
                    }
                } else {
                    return response()->json(['message' => 'Ya ha registrado su entrada', 'statusCode'=>409], 409);
                }
            } else {
                try {
                    // Registra la asistencia inicial
                    $user->events()->attach($active->id, [
                        'entry_time' => $horaActual,
                        'attendance_date' => $fechaActual,
                    ]);
                    return response()->json(['message' => 'Registro de entrada realizado de forma exitosa', 'statusCode'=>200], 200);
                } catch (Exception $e) {
                    return response()->json(['message' => 'Error al registrar la asistencia: ' . $e->getMessage(), 'statusCode'=>400], 400);
                }
            }
        } else { // Evento sin asistencia diaria
            $recentAttendanceForCurrentEvent = $user->events()
                ->wherePivot('attendance_date', $fechaActual)
                ->wherePivot('event_id', $active->id)
                ->first();
        
            if ($recentAttendanceForCurrentEvent) {
                $entryTimePlus = Carbon::parse($recentAttendanceForCurrentEvent->pivot->entry_time)->addMinutes($active->change_attendance);
                $currentTime = Carbon::now();
        
                if ($entryTimePlus->lte($currentTime)) {
                    if (!$recentAttendanceForCurrentEvent->pivot->finish_time) {
                        try {
                            $user->events()
                                ->wherePivot('attendance_date', $fechaActual)
                                ->updateExistingPivot($active->id, [
                                    'finish_time' => $horaActual,
                                ]);
                            return response()->json(['message' => 'Registro de salida finalizado con éxito', 'statusCode'=>200], 200);
                        } catch (Exception $e) {
                            return response()->json(['message' => 'Error al finalizar la asistencia: ' . $e->getMessage(), 'statusCode'=>400], 400);
                        }
                    } else {
                        return response()->json(['message' => 'Ya ha registrado su salida', 'statusCode'=>409], 409);
                    }
                } else {
                    return response()->json(['message' => 'Ya ha registrado su entrada', 'statusCode'=>409], 409);
                }
            } else {
                try {
                    $user->events()->attach($active->id, [
                        'entry_time' => $horaActual,
                        'attendance_date' => $fechaActual,
                    ]);
                    return response()->json(['message' => 'Registro de entrada realizado de forma exitosa', 'statusCode'=>200], 200);
                } catch (Exception $e) {
                    return response()->json(['message' => 'Error al registrar la asistencia: ' . $e->getMessage(), 'statusCode'=>400], 400);
                }
            }
        }
    }


    

    public function generateReportUser(Request $request)
    {
        $user = Users::where(DB::raw("CONCAT(name, ' ', last_name)"), $request->name)->first();
       
        if (!$user) {
            return response()->json(['message' => 'El Usuario no existe.',
            'statusCode' => 404,], 404);
        }
    
        // Validación de los datos del formulario
        $validator = Validator::make($request->all(), [
            'initial_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:initial_date',
            'event_id' => 'required'
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación',
                'statusCode' => 422,
                'errors' => $validator->errors()
            ], 422);
        }
    
        $event = Events::where('id', $request->event_id)->first();
        if (!$event) {
            return response()->json(['message' => 'No se encontró el evento'], 400);
        }
    
        // Obteniendo turno del usuario
        $shift = $user->shift;
        $shift_entryTime = Carbon::parse($shift->entry_time);
        $shift_finishTime = Carbon::parse($shift->finish_time);

         // Obteniendo departemento del usuario
        $department = $user->department;

        // Obtener los días de trabajo del usuario
        $workingDays = json_decode($shift->days);
   
        //obteniendo iformación del evento
        $isDailyAttendance = $event->daily_attendance;
        $eventId = $event->id;
    
        // Obtener los datos del formulario
        $startDate = Carbon::parse($request->initial_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();
    
        // Obtener los registros de asistencia del usuario dentro del rango de fechas
        $attendances = $user->events()->wherePivot('attendance_date', '>=', $startDate)
                            ->wherePivot('attendance_date', '<=', $endDate)
                            ->wherePivot('event_id', $eventId)
                            ->orderBy('attendance_date')
                            ->get()
                            ->map(function($event) use ($shift_entryTime, $shift_finishTime, $isDailyAttendance) {
                                $entryTime = Carbon::parse($event->pivot->entry_time);
                                $finishTime = $event->pivot->finish_time ? Carbon::parse($event->pivot->finish_time) : null;
    
                                if ($isDailyAttendance === 1) {
                                    // Calcular minutos tarde en valores enteros
                                    $minutesLate = intval($entryTime->greaterThan($shift_entryTime) ? $shift_entryTime->diffInMinutes($entryTime) : 0);
    
                                    // Calcular minutos antes de marcar la salida en valores enteros
                                    $minutesEarlyLeaving = intval($finishTime && $finishTime->lessThan($shift_finishTime) ? $finishTime->diffInMinutes($shift_finishTime) : 0);
    
                                    // Calcular tiempo no dedicado
                                    $timeNonDedicated = $minutesLate + $minutesEarlyLeaving;
    
                                    // Calcular horas dedicadas
                                    $dedicatedMinutes = $finishTime ? $entryTime->diffInMinutes($finishTime) : 480;
                                    $dedicatedHours = $finishTime ? $dedicatedMinutes / 60: 0; // Convertir minutos a horas
    
                                    return [
                                        'entry_time' => $event->pivot->entry_time,
                                        'finish_time' => $finishTime? $event->pivot->finish_time: "Sin marca",
                                        'attendance_date' => $event->pivot->attendance_date,
                                        'minutes_late' => $minutesLate,
                                        'minutes_early_leaving' => $minutesEarlyLeaving,
                                        'time_non_dedicated' => $timeNonDedicated,
                                        'time_dedicated' => round($dedicatedHours, 2), // Redondear a 2 decimales
                                    ];
                                } else {
                                    // Calcular horas dedicadas
                                    $dedicatedMinutes = $finishTime ? $entryTime->diffInMinutes($finishTime) : 480;
                                    $dedicatedHours = $finishTime ?  $dedicatedMinutes / 60: 0; // Convertir minutos a horas
    
                                    return [
                                        'entry_time' => $event->pivot->entry_time,
                                        'finish_time' => $finishTime? $event->pivot->finish_time: "Sin marca",
                                        'attendance_date' => $event->pivot->attendance_date,
                                        'time_dedicated' => round($dedicatedHours, 2), // Redondear a 2 decimales
                                    ];
                                }
                            });
    
        // Crear una colección de fechas entre startDate y endDate
       
    
        if ($isDailyAttendance === 1) {


            $dateRange = collect();
            for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
                // Incluir solo los días de trabajo
                if (in_array($date->dayOfWeek, $workingDays)) {
                    $dateRange->push($date->copy());
                }
            }
        
            // Array de asistencia (incluye días no registrados)
            $fullAttendances = $dateRange->map(function($date) use ($attendances, $isDailyAttendance) {
                $attendance = $attendances->firstWhere('attendance_date', $date->toDateString());
        
                if ($attendance) {
                    return $attendance;
                } else {
                    return [
                        'entry_time' => 'Sin marca',
                        'finish_time' => 'Sin marca',
                        'attendance_date' => $date->toDateString(),
                        'minutes_late' => '-',
                        'minutes_early_leaving' => '-',
                        'time_non_dedicated' => $isDailyAttendance === 1 ? 480 : 0,
                        'time_dedicated' => 0,
                    ];
                }
            });

            $totalNonDedicatedTime = $fullAttendances->sum('time_non_dedicated');  //total tiempo no dedicado               
            $totalDedicatedTime = $fullAttendances->sum('time_dedicated'); // total tiempo dedicado
    
            return response()->json([
                'message' => 'Reporte generado',
                'user_name' => $user->name . ' ' . $user->last_name,
                'shift_name' => $shift->name,
                'event_name' => $event->name,
                'department_name' => $department->name,
                'daily_attendance' => $event->daily_attendance == 1 ? 'Si' : 'No',
                'total_non_didicated_time' => $totalNonDedicatedTime,
                'total_dedicated_hours' => $totalDedicatedTime,
                'attendances' => $fullAttendances
            ], 200);
        } else {
            $totalDedicatedTime = $attendances->sum('time_dedicated'); // total tiempo dedicado
    
            return response()->json([
                'message' => 'Reporte generado',
                'user_name' => $user->name . ' ' . $user->last_name,
                'shift_name' => $shift->name,
                'event_name' => $event->name,
                'department_name' => $department->name,
                'daily_attendance' => $event->daily_attendance == 1 ? 'Si' : 'No',
                'total_dedicated_hours' => $totalDedicatedTime,
                'attendances' => $attendances
            ], 200);
        }
    }
    

    public function getDailyAttendace (){
       
        $active = Events::where('status', true)->first();
        if (!$active) {
            return response()->json(['message' => 'No hay eventos activos'], 400);
        }


 $fechaActual = Carbon::now()->format('Y-m-d');


 // Obtener las asistencias del evento activo para la fecha actual
        $recentAttendance = $active->users()
        ->wherePivot('attendance_date', $fechaActual)
        ->orderBy('pivot_entry_time', 'desc')
        ->get();

// Formatear el resultado para incluir los datos de asistencia
        $attendanceData = $recentAttendance->map(function ($user) {
        return [
            'user_name' => $user->name. ' '. $user->last_name ,
            'entry_time' => $user->pivot->entry_time,
            'finish_time' => $user->pivot->finish_time,
            'attendance_date' => $user->pivot->attendance_date,
        ];
        });


         // Contar el total de registros de asistencia
        $totalRecords = $attendanceData->count();

        // Contar la cantidad de usuarios activos
        $activeUsersCount = Users::where('status', 'Activo')->count();
        $inactiveUsersCount = Users::where('status', 'Inactivo')->count();

        
        return response()->json([
            'attendance' => $attendanceData,
            'total_records' => $totalRecords,
            'active_users_count' => $activeUsersCount,
            'inactive_users_count' => $inactiveUsersCount
        ], 200);                       
    }


    //Reporte general



    public function generateReportUsers(Request $request)
    {
    
        // Validación de los datos del formulario
        $validator = Validator::make($request->all(), [
            'department_id' => 'required',
            'initial_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:initial_date',
            'event_id' => 'required'
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación',
                'statusCode' => 422,
                'errors' => $validator->errors()
            ], 422);
        }
    
        $event = Events::where('id', $request->event_id)->first();
        if (!$event) {
            return response()->json(['message' => 'No se encontró el evento', 'statusCode' => 404,], 404);
        }
        

        if($request->department_id == 0){
            $users = Users::all();
        }else{
            $users = Users::where('department_id', $request->department_id)->get();
        }
       
       

        if (!$users) {
            return response()->json(['message' => 'No se encontraron usuarios', 'statusCode' => 404,], 404);
        }

       
        $result = [];

        foreach ($users as $user) {

       // Obteniendo horarios del usuario
        $shift = $user->shift;
        $shift_entryTime = Carbon::parse($shift->entry_time);
        $shift_finishTime = Carbon::parse($shift->finish_time);
        $shift_mothly_late_allowance = $shift->mothly_late_allowance;

         // Obteniendo departemento del usuario
         $department = $user->department;

         // Obtener los días de trabajo del usuario
         $workingDays = json_decode($shift->days);
         
        //obteniendo datos de evento
        $isDailyAttendance = $event->daily_attendance;
        $eventId = $event->id;
    
        // Obtener los datos del formulario
        $startDate = Carbon::parse($request->initial_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();
    
        // Obtener los registros de asistencia del usuario dentro del rango de fechas
        $attendances = $user->events()->wherePivot('attendance_date', '>=', $startDate)
                            ->wherePivot('attendance_date', '<=', $endDate)
                            ->wherePivot('event_id', $eventId)
                            ->orderBy('attendance_date')
                            ->get()
                            ->map(function($event) use ($shift_entryTime, $shift_finishTime, $isDailyAttendance) {
                                $entryTime = Carbon::parse($event->pivot->entry_time);
                                $finishTime = $event->pivot->finish_time ? Carbon::parse($event->pivot->finish_time) : null;
    
                                if ($isDailyAttendance === 1) {
                                    // Calcular minutos tarde en valores enteros
                                    $minutesLate = intval($entryTime->greaterThan($shift_entryTime) ? $shift_entryTime->diffInMinutes($entryTime) : 0);
    
                                    // Calcular minutos antes de marcar la salida en valores enteros
                                    $minutesEarlyLeaving = intval($finishTime && $finishTime->lessThan($shift_finishTime) ? $finishTime->diffInMinutes($shift_finishTime) : 0);
    
                                    // Calcular tiempo no dedicado
                                    $timeNonDedicated = $minutesLate + $minutesEarlyLeaving;
    
                                    // Calcular horas dedicadas
                                    $dedicatedMinutes = $finishTime ? $entryTime->diffInMinutes($finishTime) : 480;
                                    $dedicatedHours = $finishTime ? $dedicatedMinutes / 60: 0; // Convertir minutos a horas
    
                                    return [
                                        'entry_time' => $event->pivot->entry_time,
                                        'finish_time' => $finishTime? $event->pivot->finish_time: "Sin marca",
                                        'attendance_date' => $event->pivot->attendance_date,
                                        'minutes_late' => $minutesLate,
                                        'minutes_early_leaving' => $minutesEarlyLeaving,
                                        'time_non_dedicated' => $timeNonDedicated,
                                        'time_dedicated' => round($dedicatedHours, 2), // Redondear a 2 decimales
                                    ];
                                } else {
                                    // Calcular horas dedicadas
                                    $dedicatedMinutes = $finishTime ? $entryTime->diffInMinutes($finishTime) : 480;
                                    $dedicatedHours = $finishTime ?  $dedicatedMinutes / 60: 0; // Convertir minutos a horas
    
                                    return [
                                        'entry_time' => $event->pivot->entry_time,
                                        'finish_time' => $finishTime? $event->pivot->finish_time: "Sin marca",
                                        'attendance_date' => $event->pivot->attendance_date,
                                        'time_dedicated' => round($dedicatedHours, 2), // Redondear a 2 decimales
                                    ];
                                }
                            });
           
        if ($isDailyAttendance === 1) {
            
           
            // Crear una colección de fechas entre startDate y endDate
            $dateRange = collect();
            for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
                // Incluir solo los días de trabajo
                if (in_array($date->dayOfWeek, $workingDays)) {
                    $dateRange->push($date->copy());
                }
            }
        
            // Array de asistencia (incluye días no registrados)
               $fullAttendances = $dateRange->map(function($date) use ($attendances, $isDailyAttendance) {
                $attendance = $attendances->firstWhere('attendance_date', $date->toDateString());
        
                if ($attendance) {
                    return $attendance;
                } else {
                   
                    return [
                        'entry_time' => 'Sin marca',
                        'finish_time' => 'Sin marca',
                        'attendance_date' => $date->toDateString(),
                        'minutes_late' => '-',
                        'minutes_early_leaving' => '-',
                        'time_non_dedicated' => $isDailyAttendance === 1 ? 480 : 0,
                        'time_dedicated' => 0,
                    ];

                }
            });

            $totalNonDedicatedTime = $fullAttendances->sum('time_non_dedicated');  //total tiempo no dedicado               
            $totalDedicatedTime = $fullAttendances->sum('time_dedicated'); // total tiempo dedicado
    
            if ($totalNonDedicatedTime > $shift_mothly_late_allowance) {
            $result[] = [
                'message' => 'Reporte generado',
                'user_name' => $user->name . ' ' . $user->last_name,
                'shift_name' => $shift->name,
                'event_name' => $event->name,
                'department_name' => $department->name,
                'mothly_late_allowance' =>  $shift_mothly_late_allowance, 
                'daily_attendance' => $event->daily_attendance == 1 ? 'Si' : 'No',
                'total_non_didicated_time' => $totalNonDedicatedTime,
                'total_dedicated_hours' => $totalDedicatedTime,
                'attendances' => $fullAttendances->filter(function ($attendance) {
                    return $attendance['time_non_dedicated'] > 0;
                })->values()
            ];
        }
        } else {
            $totalDedicatedTime = $attendances->sum('time_dedicated'); // total tiempo dedicado
    
            $result[] = [
                'message' => 'Reporte generado',
                'daily_attendance' => $event->daily_attendance == 1 ? 'Si' : 'No',
                'user_name' => $user->name . ' ' . $user->last_name,
                'department_name' => $department->name,
                'event_name' => $event->name,
                'total_dedicated_hours' => $totalDedicatedTime,
            ];
        }                     

        

        }


        return response()->json($result, 200);


    
       
    
       
       
    
    }





}

