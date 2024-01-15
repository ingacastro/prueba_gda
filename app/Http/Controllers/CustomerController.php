<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use DateTime;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;


class CustomerController extends Controller
{
    //
    public function index()
    {
        $customer_show = [];
        $customers = Customer::where('status', 'A')->with(['commune.region' => function($query){
            $query->select('id_reg','description');
        }])
        ->select('id_com', 'name', 'last_name', 'address')
        ->get();

        foreach($customers as $customer){
            $customer_result = [];
            $customer_result['name'] = $customer->name; 
            $customer_result['last_name'] = $customer->last_name; 
            $customer_result['address'] = $customer->address ? $customer->address : null; 
            $customer_result['commune'] = $customer->commune->description;
            $customer_result['region'] = $customer->commune->region->description; 
            $customer_show[] = $customer_result;
        }

        //return $customer_show;
        
        Log::info('inicio');
        Log::info('consulta');
        Log::info('ip: '. \Request::ip());
        Log::info('fin');

        return response()->json([
            'response' => 'OK',
            'code' => 200,
            'data' => $customer_show
        ], 200);
    }
 
    public function show($dni)
    {
        $customer = Customer::where('dni', $dni)
            //->with('commune')
            ->where('status', 'A')
            ->select('id_com', 'name', 'last_name', 'address')
            //->with('region')
            ->with(['commune.region' => function($query){
                $query->select('id_reg','description');
            }])
            ->first();

        //return $customer;
        if($customer){
            $customer_result = [];
            $customer_result['name'] = $customer->name; 
            $customer_result['last_name'] = $customer->last_name; 
            $customer_result['address'] = $customer->address ? $customer->address : null; 
            $customer_result['commune'] = $customer->commune->description;
            $customer_result['region'] = $customer->commune->region->description;
        } else {
            return response()->json([
                'response' => 'OK',
                'code' => 404,
                'message' => 'Registro No Existe'
            ], 404);
        }
        
        return response()->json([
            'response' => 'OK',
            'code' => 200,
            'data' => $customer_result
        ], 200);

        //return $customer;
    }

    public function store(Request $request)
    {
        
        $validator = Validator::make($request->all(),[
            'dni' => 'required|numeric',
            'id_reg' => 'required|numeric',
            'id_com' => 'required|numeric',
            'email' => 'required|unique:users|email',
            'name' => 'required|string',
            'last_name' => 'required|string',
            'address' => 'string'
        ]);

        if ($validator->fails()) {
            Log::info('inicio');
            Log::info('Error en insertar');
            Log::info($validator->errors());
            Log::info('ip: '.$request->ip());
            Log::info('fin');
            return $validator->errors();
        }
        

        try {

            $customer = new Customer; 

            // Recibo todos los datos del formulario de la vista 'crear.blade.php'
            $customer->dni = $request->dni;
            $customer->id_reg = $request->id_reg;
            $customer->id_com = $request->id_com;
            $customer->email = $request->email;
            $customer->name = $request->name;
            $customer->last_name = $request->last_name;
            $customer->address = $request->address;
            $customer->status = $request->status;

            // Guardamos la fecha de creación del registro 
            $date_reg = \Carbon\Carbon::now();
            $customer->date_reg = $date_reg;

            $customer->save();

            Log::info('inicio');
            Log::info('insertar');
            Log::info($customer);
            Log::info('ip: '.$request->ip());
            Log::info('fin');
        
        } catch (\Exception $e) {
            $errors = [
                'message' => $e->getMessage(),
                'line' => __LINE__.' '.$e->getLine(),
                'file' => __FILE__.' '.$e->getFile(),
                'clientIpAddress' => $request->ip(),
                '\Request::ip()' => \Request::ip(),
                '\request()->ip()' =>  \request()->ip(),
                'clientIpAddress' =>  request()->getClientIp(),
            ]; 


            Log::info('inicio');
            Log::info('error en insertar');
            Log::debug($errors);
            Log::info($errors);
            Log::info('fin');

            return $errors;
         
        }

        return response()->json([
            'response' => 'OK',
            'code' => 201,
            'data' => $customer
        ], 201);

        return response()->json($customer, 201);
    }

    public function update(Request $request, Customer $customer)
    {
        $customer->update($request->all());

        return response()->json($customer, 200);
    }

    public function delete($dni)
    {
        $customer = Customer::where('dni', $dni)->whereIn('status', ['A', 'I']);
        
        if($customer->count() > 0){
            $customer->update(['status' => '3']);
            return response()->json([
                'response' => 'OK',
                'code' => 200
            ], 200);
        } else {
            return response()->json([
                'response' => 'OK',
                'code' => 404,
                'message' => 'Registro No Existe'
            ], 404);
        }
        

        //return response()->json(null, 204);
    }
}
