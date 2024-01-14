<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use DateTime;


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

        return $customer_show;
        
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

        return $customer;
        return response()->json([
            'response' => 'OK',
            'code' => 200,
            'data' => $customer
        ], 200);

        return $customer;
    }

    public function store(Request $request)
    {
        try {
            //return Customer::create($request->all());
            //return $request;
            
            //$customer = Customer::create($request->all());

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
            //$date_reg = (new DateTime)->getTimestamp();
            $date_reg = \Carbon\Carbon::now();
            //return $date_reg;
            $customer->date_reg = $date_reg;

            // Inserto todos los datos en mi tabla 'customers' 
            $customer->save();
        
        } catch (Throwable $e) {
            return $e; 
            return report($e);
     
            return false;
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
                'code' => 200,
                //'data' => $customer
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
