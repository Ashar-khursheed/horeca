<?php
// File: app/Http/Controllers/EliteShipmentController.php
namespace Botble\Ecommerce\Http\Controllers;
use Kris\LaravelFormBuilder\Facades\FormBuilder; // Import the FormBuilder facade

use Botble\Ecommerce\Models\EliteShipment;
use Botble\Ecommerce\Forms\ShipmentInfoForm;
use Botble\Base\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EliteShipmentController extends BaseController
{
    public function create()
    {
        $form = FormBuilder::create(ShipmentInfoForm::class, [
            'method' => 'POST',
            'url' => route('eliteshipment.store'),
        ]);

        return view('eliteshipment.create', compact('form'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'shipper_name' => 'required|string',
            'shipper_address' => 'required|string',
            'shipper_area' => 'required|string',
            'shipper_city' => 'required|string',
            'shipper_telephone' => 'required|string',
            'receiver_name' => 'required|string',
            'receiver_address' => 'required|string',
            'receiver_area' => 'required|string',
            'receiver_city' => 'required|string',
            'receiver_telephone' => 'required|string',
            'receiver_mobile' => 'required|string',
            'receiver_email' => 'required|email',
            'shipping_reference' => 'required|string',
            'orders' => 'required|string',
            'item_type' => 'required|string',
            'item_description' => 'required|string',
            'item_value' => 'required|numeric',
            'dangerousGoodsType' => 'nullable|string',
            'weight_kg' => 'required|numeric',
            'no_of_pieces' => 'required|numeric',
            'service_type' => 'required|string',
            'cod_value' => 'nullable|numeric',
            'service_date' => 'required|date',
            'service_time' => 'required|date_format:H:i', // Assuming H:i format for time
            'created_by' => 'required|string',
            'special' => 'nullable|string',
            'order_type' => 'required|string',
            'ship_region' => 'required|string',
        ]);


        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Create a new shipment record
        $shipment = new EliteShipment();
        $shipment->shipper_name = $request->shipper_name;
        $shipment->shipper_address = $request->shipper_address;
        $shipment->shipper_area = $request->shipper_area;
        $shipment->shipper_city = $request->shipper_city;
        $shipment->shipper_telephone = $request->shipper_telephone;
        $shipment->receiver_name = $request->receiver_name;
        $shipment->receiver_address = $request->receiver_address;
        $shipment->receiver_address2 = $request->receiver_address2;
        $shipment->receiver_area = $request->receiver_area;
        $shipment->receiver_city = $request->receiver_city;
        $shipment->receiver_telephone = $request->receiver_telephone;
        $shipment->receiver_mobile = $request->receiver_mobile;
        $shipment->receiver_email = $request->receiver_email;
        $shipment->shipping_reference = $request->shipping_reference;
        $shipment->orders = $request->orders;
        $shipment->item_type = $request->item_type;
        $shipment->item_description = $request->item_description;
        $shipment->item_value = $request->item_value;
        $shipment->dangerousGoodsType = $request->dangerousGoodsType;
        $shipment->weight_kg = $request->weight_kg;
        $shipment->no_of_pieces = $request->no_of_pieces;
        $shipment->service_type = $request->service_type;
        $shipment->cod_value = $request->cod_value;
        $shipment->service_date = $request->service_date;
        $shipment->service_time = $request->service_time;
        $shipment->created_by = $request->created_by;
        $shipment->special = $request->special;
        $shipment->order_type = $request->order_type;
        $shipment->ship_region = $request->ship_region;

        // Save the shipment record
        $shipment->save();

        // Redirect with success message
        return redirect()->route('eliteshipment.create')->with('success', 'Shipment created successfully!');
    }
}
