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
        $customer = Customer::all();
        return response()->json([
            'response' => 'OK',
            'code' => 200,
            'data' => $customer
        ], 200);
    }
 
    public function show(Customer $customer)
    {
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
            //"date_reg": "2024-01-13",
            $customer->status = $request->status;
            
            // Almacenos la imagen en la carpeta publica especifica, esto lo veremos más adelante 
            //$customer->img = $request->file('img')->store('/'); 

            // Guardamos la fecha de creación del registro 
            //$date_reg = (new DateTime)->getTimestamp();
            $date_reg = \Carbon\Carbon::now();
            //return $date_reg;
            $customer->date_reg = $date_reg;

            // Inserto todos los datos en mi tabla 'productos' 
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
        $customer = Customer::where('dni', $dni);
        $customer->update(['status' => '3']);
        return response()->json([
            'response' => 'OK',
            'code' => 200,
            //'data' => $customer
        ], 200);

        //return response()->json(null, 204);
    }
}
