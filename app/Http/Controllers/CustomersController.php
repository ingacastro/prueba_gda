<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use Session;
use Redirect;
use App\Http\Requests;
use App\Http\Controllers\Controller;
//use App\Http\Requests\ItemCreateRequest;
//use App\Http\Requests\ItemUpdateRequest;
use Illuminate\Support\Facades\Validator;
use DB;
use Input;
use Storage;


class CustomersController extends Controller
{
    //

    
    // Listar todos los productos en la vista principal 
    public function index()
    {
        $customer = Customer::all();
        return response()->json([
            'response' => 'OK',
            'code' => 200,
            'data' => $customer
        ], 200);
        return view('admin.customer.index', compact('customer')); 
    }

    // Crear un Registro (Create) 
    public function crear()
    {
        $customers = Customer::all();
        return view('admin.customer.crear', compact('customer'));
    }
    
    // Proceso de Creación de un Registro 
    public function store(ItemCreateRequest $request)
    {
        // Instancio al modelo Productos que hace llamado a la tabla 'productos' de la Base de Datos
        $customer = new Customer; 

        // Recibo todos los datos del formulario de la vista 'crear.blade.php'
        $customer->name = $request->name;
        $customer->last_name = $request->last_name;
        $customer->address = $request->address;
        $customer->status = $request->status;
        
        // Almacenos la imagen en la carpeta publica especifica, esto lo veremos más adelante 
        //$customer->img = $request->file('img')->store('/'); 

        // Guardamos la fecha de creación del registro 
        $customer->created_at = (new DateTime)->getTimestamp(); 

        // Inserto todos los datos en mi tabla 'productos' 
        $customer->save();

        // Hago una redirección a la vista principal con un mensaje 
        return redirect('admin/customer')->with('message','Guardado Satisfactoriamente !'); 
    }

    
    // Leer Registro por 'id' (Read) 
    public function show($id)
    {
        $customers = Customer::find($id);
        return view('admin.customer.detalles', compact('customer')); 
    }
    
    //  Actualizar un registro (Update)
    public function actualizar($id)
    {
        $customer = Customer::find($id);
        return view('admin/customer.actualizar',['customer'=>$customer]);
    }

    
    // Proceso de Actualización de un Registro (Update)
    public function update(ItemUpdateRequest $request, $id)
    {        
        // Recibo todos los datos desde el formulario Actualizar
        $customer = Customer::find($id);
        $customer->name = $request->name;
        $customer->last_name = $request->last_name;
        $customer->address = $request->address;

        // Recibo la imagen desde el formulario Actualizar
        if ($request->hasFile('img')) {
            $customer->img = $request->file('img')->store('/');
        }

        // Guardamos la fecha de actualización del registro 
        $customer->updated_at = (new DateTime)->getTimestamp(); 
            
        // Actualizo los datos en la tabla 'productos'
        $customer->save();

        // Muestro un mensaje y redirecciono a la vista principal 
        Session::flash('message', 'Editado Satisfactoriamente !');
        return Redirect::to('admin/customer');
    }
    
    // Eliminar un Registro 
    public function eliminar($id)
    {
        // Indicamos el 'id' del registro que se va Eliminar
        $customer = Customer::find($id);

        // Elimino la imagen de la carpeta 'uploads', esto lo veremos más adelante
        $imagen = explode(",", $customer->img);
        Storage::delete($imagen);
            
        // Elimino el registro de la tabla 'productos' 
        Customer::destroy($id);

        // Opcional: Si deseas guardar la fecha de eliminación de un registro, debes mantenerlo en 
        // una tabla llamada por ejemplo 'productos_eliminados' y alli guardas su fecha de eliminación 
        // $productos->deleted_at = (new DateTime)->getTimestamp();
            
        // Muestro un mensaje y redirecciono a la vista principal 
        Session::flash('message', 'Eliminado Satisfactoriamente !');
        return Redirect::to('admin/customer');
    }

}

