<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Customer, Region, Commune};
use DateTime;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class CustomerController extends Controller
{
    //
    public function index()
    {
        Log::warning('User is accessing all the Customers', ['user' => Auth::user()->id]);
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
            'success' => 'true',
            'code' => 200,
            'data' => $customer_show
        ], 200);
    }
 
    public function show($dni)
    {

        Log::info('User is accessing a single customer', ['user' => Auth::user()->id, 'customer' => $dni]);
        
        $validator = Validator::make(['dni' => $dni],[
            'dni' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            Log::info('inicio');
            Log::info('Error en consultar');
            Log::info($validator->errors());
            Log::info('ip: '. \Request::ip());
            Log::info('fin');
            return response()->json([
                'success' => 'false',
                'code' => 500,
                'errors' => $validator->errors()
            ], 500);
        }
        $customer = Customer::where('dni', $dni)
            ->where('status', 'A')
            ->select('id_com', 'name', 'last_name', 'address')
            ->with(['commune.region' => function($query){
                $query->select('id_reg','description');
            }])
            ->first();

        if($customer){
            $customer_result = [];
            $customer_result['name'] = $customer->name; 
            $customer_result['last_name'] = $customer->last_name; 
            $customer_result['address'] = $customer->address ? $customer->address : null; 
            $customer_result['commune'] = $customer->commune->description;
            $customer_result['region'] = $customer->commune->region->description;
        } else {
            return response()->json([
                'success' => 'false',
                'code' => 404,
                'message' => 'Registro No Existe'
            ], 404);
        }
        
        return response()->json([
            'success' => 'true',
            'code' => 200,
            'data' => $customer_result
        ], 200);

    }

    public function store(Request $request)
    {
        Log::warning('User is trying to create a single customer', ['user' => Auth::user()->id, 'data' => $request->except('password')]);
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
            
            return response()->json([
                'success' => 'false',
                'code' => 500,
                'erros' => $validator->errors()
            ], 500);
        }
        
        $commune = $this->searchIdCom($request->id_com);
        $region = $this->searchIdReg($request->id_reg);

        if(!$commune){
            Log::warning('Customer could not be created caused by invalid customer data', ['commune' => $request->id_com]);
            return 'Commune no existe';
        }

        if(!$region){
            Log::warning('Customer could not be created caused by invalid customer data', ['region' => $request->id_reg]);
            return 'Region no existe';
        }

        try {

            $customer = new Customer; 
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

            if ($customer->save()) {
                Log::info('User create a single customer successfully', [
                    'user' => Auth::user()->id,
                    'customer' => $customer,
                    'ip' => $request->ip()
                ]);
            }
        
        } catch (\Exception $e) {
            $errors = [
                'message' => $e->getMessage(),
                'line' => __LINE__.' '.$e->getLine(),
                'file' => __FILE__.' '.$e->getFile(),
                'clientIpAddress' => $request->ip()
            ]; 

            Log::warning('Customer could not be created caused by invalid customer data', [
                'user' => Auth::user()->id,
                'data' => $request->except('password'),
                'errors' => $errors
            ]);

            return response()->json([
                'success' => 'false',
                'code' => 500,
                'errors' => $errors
            ], 500);
         
        }

        return response()->json([
            'success' => 'true',
            'code' => 201,
            'data' => $customer
        ], 201);
    }

    public function update(Request $request, Customer $customer)
    {
        $customer->update($request->all());

        return response()->json($customer, 200);
    }

    public function delete($dni)
    {
        Log::warning('User is trying to delete a single customer', ['user' => Auth::user()->id, 'customer' => $dni]);
        
        $validator = Validator::make(['dni' => $dni],[
            'dni' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            Log::info('inicio');
            Log::info('Error en eliminar');
            Log::info($validator->errors());
            Log::info('ip: '. \Request::ip());
            Log::info('fin');
            return response()->json([
                'success' => 'false',
                'code' => 500,
                'errors' => $validator->errors()
            ], 500);
        }

        $customer = Customer::where('dni', $dni)->whereIn('status', ['A', 'I']);
        
        if($customer->count() > 0){
            Log::info('User deleted a single customer successfully', ['user' => Auth::user()->id, 'customer' => $dni]);
            $customer->update(['status' => '3']);
            return response()->json([
                'success' => 'true',
                'code' => 200
            ], 200);
        } else {
            Log::error('Customer not found by user for deleting', ['user' => Auth::user()->id, 'customer' => $dni]);
            return response()->json([
                'success' => 'true',
                'code' => 404,
                'message' => 'Registro No Existe'
            ], 404);
        }
    }

    public function searchIdReg($id_reg){
        return Region::where('id_reg', $id_reg)->first();
    }

    public function searchIdCom($id_com){
        return Commune::where('id_com', $id_com)->first();
    }
}
