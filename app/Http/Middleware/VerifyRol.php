<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Roles;
use Illuminate\Support\Facades\Auth;



class VerifyRol

{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */

     public function handle(Request $request, Closure $next, ...$roles)
     {
         // Verificar si el usuario está autenticado con Sanctum
         if (Auth::guard('sanctum')->check()) {
             // Obtener el usuario autenticado
             $user = Auth::guard('sanctum')->user();
             
             // Obtener el id del rol del usuario  
             $roleId = $user->rol_id;
             
             // Buscar el nombre del rol en la tabla de roles
             $userRole = Roles::find($roleId)->name ?? null;
             
             // Verificar si el usuario tiene el rol especificado
             if (in_array($userRole, $roles)) {
                 // Si el usuario tiene el rol requerido, continuar con la solicitud
                 return $next($request);
             }
             
             // Si el usuario no tiene el rol requerido, responder con un error
             return response()->json(['message' => 'No tienes permiso para acceder a esta ruta'], 403);
         }
         
         // Si el usuario no está autenticado, responder con un error
         return response()->json(['message' => 'Debe iniciar sesión para acceder a esta ruta'], 401);
     }
   

    
}
