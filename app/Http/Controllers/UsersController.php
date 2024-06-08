<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Users;
use Illuminate\Support\Facades\Validator; 
use LaravelQRCode\Facades\QRCode;
use Illuminate\Support\Str;


class UsersController extends Controller
{
    public function index()
    {
        $users = Users::all()->map(function ($user) {
            $user->profile_image = asset('http://localhost:8000/storage/images/profiles/' . $user->profile_image); 
            $user->qr_image = asset('http://localhost:8000/storage/images/qrcodes/' . $user->qr_image); 
            return $user;
        });
        return response()->json($users);
    }

    public function show(string $id) {
        $user = Users::find($id);
       
        if (!$user) {
            return response()->json(['message' => 'El horario no existe.',
            'statusCode' => 404,], 404);
        }

        $user->profile_image = asset('http://localhost:8000/storage/images/profiles/' . $user->profile_image); 
        $user->qr_image = asset('http://localhost:8000/storage/images/qrcodes/' . $user->qr_image); 
      
        return response()->json($user);
      }
    

      public function store(Request $request)
      {
          do {
              $id = 'qr-' . Str::random(7); // Generar una cadena aleatoria de longitud 7
          } while (Users::where('id', $id)->exists()); // Verifica si el ID ya existe en la base de datos
          
          // Verificar si se envió un archivo
          if(!$request->hasFile('profile_image')) {
              return response()->json(['message' => 'No se envió ningún archivo', 'statusCode'=>404], 404);
          }
         
          $validator = Validator::make($request->all(), [
              'name' => 'required|string|max:255',
              'last_name' => 'required|string|max:255',
              'age' => 'required|integer',
              'gender' => 'required|string|max:255',
              'email' => 'required|string|email|max:255|unique:users',
              'address' => 'required|string|max:255',
              'phone_number' => 'required|integer',
              'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
              'shift_id' => 'required|integer',
              'department_id' => 'required|integer',
              'status' => 'required|string',
          ]);
      
          if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación',
                'statusCode' => 422,
                'messageDetail' => $validator->errors()->all()
            ], 422);
          }                                                                       
          
          // Obtener el archivo de la solicitud
          $profileImage = $request->file('profile_image');
          $extension = $profileImage->getClientOriginalExtension();
          
          //Nombres de imagen archivo 
          $profileImageName = 'img-'.$id. '.'. $extension;
      
          // Crear usuario
          $user = Users::create([
              'id' =>  $id,
              'name' =>  $request->name,
              'last_name' =>  $request->last_name,
              'age' =>  $request->age,
              'gender' =>  $request->gender,
              'email' =>  $request->email,
              'address' =>  $request->address,
              'phone_number' =>  $request->phone_number,
              'profile_image' => $profileImageName,
              'qr_image' => $id. '.png',
              'shift_id' => $request->shift_id,
              'department_id' =>  $request->department_id,
              'status' => $request->status
          ]);
      
          // Guardar imagen archivos en servidor
          $profileImage->storeAs('public/images/profiles', $profileImageName);
        
          // Generar imagen QR y guardar en servidor
          $path = storage_path('app/public/images/qrcodes/'. $id .'.png');
        
          QRCode::text($id)
                ->setOutfile($path)
                ->png();
      
          return response()->json(['message'=> 'Usuario creado correctamente', 'data'=>$user], 201);
      }
      

       
        public function update(Request $request, $id)
    {



        $response = ["status" => 404, "msg" => ""];
        $user = Users::where('id', $id)->first();

        if (!$user) {
            return response()->json(['message' => 'El horario no existe.',
            'statusCode' => 404,], 404);
        }


      

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'age' => 'required|integer',
            'gender' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'address' => 'required|string|max:255',
            'phone_number' => 'required|integer',
            'shift_id' => 'required|integer',
            'department_id' => 'required|integer',
            'status' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación',
                'statusCode' => 422,
                'messageDetail' => $validator->errors()->all()
            ], 422);
        }


        if($request->hasFile('profile_image')) {
            $profileImage = $request->file('profile_image');
            $extension = $profileImage->getClientOriginalExtension();
            //Nombres de imagen archivo 
            $profileImageName = 'img-'.$user->id. '.'. $extension;

            $profileImagePath = storage_path('app/public/images/profiles/' . $user->profile_image);

            if (file_exists($profileImagePath)) {
                unlink($profileImagePath);   
            }

            $profileImage->storeAs('public/images/profiles', $profileImageName);
            $user->profile_image = $profileImageName;
        }


        $user->name = $request->name;
        $user->last_name  = $request->last_name;
        $user->age  = $request->age;
        $user->gender  = $request->gender;
        $user->email  = $request->email;
        $user->address  = $request->address;
        $user->phone_number  = $request->phone_number;
        $user->shift_id  = $request->shift_id;
        $user->department_id  = $request->department_id ;
        $user->status  = $request->status;
        $user->save();


        return response()->json(['message'=> 'Usuario Actualizado correctamente', 'data'=>$user], 201);
        
    }

 


    public function destroy($id)
    {
        $user = Users::find($id);

        if (!$user ) {
            return response()->json(['message' => 'El horario no existe.',
            'statusCode' => 404,], 404);
        }

        $rowsAffected = Users::destroy($id);

        $response = [
            "statusCode" => 201,
            "message" => "Rol eliminado exitosamente",
            "affected" => $rowsAffected
        ];

      
       
        $profileImageName = $user->profile_image;
        $qrImageName = $user->qr_image;;

        $profileImagePath = storage_path('app/public/images/profiles/' . $profileImageName);
        $qrImagePath = storage_path('app/public/images/qrcodes/' . $qrImageName);

        if (file_exists($profileImagePath)) {
            unlink($profileImagePath);   
        }

        if (file_exists( $qrImagePath)) {
            unlink( $qrImagePath);   
        }

        return response()->json($response, 201);
    }
}
