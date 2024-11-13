{{-- <!-- File: resources/views/eliteshipment/create.blade.php -->

@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Create New Shipment</h1>

    <!-- Success message -->
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <!-- Form to create shipment -->
    {!! Form::open(['route' => 'eliteshipment.store', 'method' => 'POST']) !!}

    <!-- Render fields dynamically using the form setup -->
    @foreach ($form->getFields() as $field)
        {!! $field->render() !!}
    @endforeach

    <button type="submit">Submit</button>

    {!! Form::close() !!}
</div>
@endsection --}}


<!-- File: resources/views/eliteshipment/create.blade.php -->

{{-- @extends('layouts.app')

@section('content')
<div class="container">
    <h1>Create New Shipment</h1>

    <!-- Success message -->
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <!-- Form to create shipment -->
    <form method="POST" action="{{ route('eliteshipment.store') }}">
        @csrf

        <!-- Render form fields dynamically -->
        <div class="form-group">
            <label for="shipper_name">Shipper Name:</label>
            <input type="text" class="form-control" id="shipper_name" name="shipper_name" placeholder="Enter shipper name" value="{{ old('shipper_name') }}" required>
        </div>

        <div class="form-group">
            <label for="shipper_address">Shipper Address:</label>
            <input type="text" class="form-control" id="shipper_address" name="shipper_address" placeholder="Enter shipper address" value="{{ old('shipper_address') }}" required>
        </div>

        <div class="form-group">
            <label for="shipper_area">Shipper Area:</label>
            <input type="text" class="form-control" id="shipper_area" name="shipper_area" placeholder="Enter shipper area" value="{{ old('shipper_area') }}" required>
        </div>

        <div class="form-group">
            <label for="shipper_city">Shipper City:</label>
            <input type="text" class="form-control" id="shipper_city" name="shipper_city" placeholder="Enter shipper city" value="{{ old('shipper_city') }}" required>
        </div>

        <div class="form-group">
            <label for="shipper_telephone">Shipper Telephone:</label>
            <input type="text" class="form-control" id="shipper_telephone" name="shipper_telephone" placeholder="Enter shipper telephone" value="{{ old('shipper_telephone') }}" required>
        </div>

        <div class="form-group">
            <label for="receiver_name">Receiver Name:</label>
            <input type="text" class="form-control" id="receiver_name" name="receiver_name" placeholder="Enter receiver name" value="{{ old('receiver_name') }}" required>
        </div>

        <div class="form-group">
            <label for="receiver_address">Receiver Address:</label>
            <input type="text" class="form-control" id="receiver_address" name="receiver_address" placeholder="Enter receiver address" value="{{ old('receiver_address') }}" required>
        </div>

        <div class="form-group">
            <label for="receiver_address2">Receiver Address 2:</label>
            <input type="text" class="form-control" id="receiver_address2" name="receiver_address2" placeholder="Enter additional receiver address (optional)" value="{{ old('receiver_address2') }}">
        </div>

        <div class="form-group">
            <label for="receiver_area">Receiver Area:</label>
            <input type="text" class="form-control" id="receiver_area" name="receiver_area" placeholder="Enter receiver area" value="{{ old('receiver_area') }}" required>
        </div>

        <div class="form-group">
            <label for="receiver_city">Receiver City:</label>
            <input type="text" class="form-control" id="receiver_city" name="receiver_city" placeholder="Enter receiver city" value="{{ old('receiver_city') }}" required>
        </div>

        <div class="form-group">
            <label for="receiver_telephone">Receiver Telephone:</label>
            <input type="text" class="form-control" id="receiver_telephone" name="receiver_telephone" placeholder="Enter receiver telephone" value="{{ old('receiver_telephone') }}" required>
        </div>

        <div class="form-group">
            <label for="receiver_mobile">Receiver Mobile:</label>
            <input type="text" class="form-control" id="receiver_mobile" name="receiver_mobile" placeholder="Enter receiver mobile" value="{{ old('receiver_mobile') }}" required>
        </div>

        <div class="form-group">
            <label for="receiver_email">Receiver Email:</label>
            <input type="email" class="form-control" id="receiver_email" name="receiver_email" placeholder="Enter receiver email" value="{{ old('receiver_email') }}" required>
        </div>

        <div class="form-group">
            <label for="shipping_reference">Shipping Reference:</label>
            <input type="text" class="form-control" id="shipping_reference" name="shipping_reference" placeholder="Enter shipping reference" value="{{ old('shipping_reference') }}" required>
        </div>

        <div class="form-group">
            <label for="orders">Orders:</label>
            <input type="text" class="form-control" id="orders" name="orders" placeholder="Enter order details" value="{{ old('orders') }}" required>
        </div>

        <div class="form-group">
            <label for="item_type">Item Type:</label>
            <input type="text" class="form-control" id="item_type" name="item_type" placeholder="Enter item type" value="{{ old('item_type') }}" required>
        </div>

        <div class="form-group">
            <label for="item_description">Item Description:</label>
            <input type="text" class="form-control" id="item_description" name="item_description" placeholder="Enter item description" value="{{ old('item_description') }}" required>
        </div>

        <div class="form-group">
            <label for="item_value">Item Value:</label>
            <input type="number" class="form-control" id="item_value" name="item_value" placeholder="Enter item value" value="{{ old('item_value') }}" required>
        </div>

        <div class="form-group">
            <label for="dangerousGoodsType">Dangerous Goods Type:</label>
            <input type="text" class="form-control" id="dangerousGoodsType" name="dangerousGoodsType" placeholder="Enter dangerous goods type" value="{{ old('dangerousGoodsType') }}">
        </div>

        <div class="form-group">
            <label for="weight_kg">Weight (kg):</label>
            <input type="number" class="form-control" id="weight_kg" name="weight_kg" placeholder="Enter weight in kg" value="{{ old('weight_kg') }}" required>
        </div>

        <div class="form-group">
            <label for="no_of_pieces">No of Pieces:</label>
            <input type="number" class="form-control" id="no_of_pieces" name="no_of_pieces" placeholder="Enter number of pieces" value="{{ old('no_of_pieces') }}" required>
        </div>

        <div class="form-group">
            <label for="service_type">Service Type:</label>
            <input type="text" class="form-control" id="service_type" name="service_type" placeholder="Enter service type" value="{{ old('service_type') }}" required>
        </div>

        <div class="form-group">
            <label for="cod_value">COD Value:</label>
            <input type="number" class="form-control" id="cod_value" name="cod_value" placeholder="Enter COD value" value="{{ old('cod_value') }}">
        </div>

        <div class="form-group">
            <label for="service_date">Service Date:</label>
            <input type="date" class="form-control" id="service_date" name="service_date" placeholder="Enter service date" value="{{ old('service_date') }}" required>
        </div>

        <div class="form-group">
            <label for="service_time">Service Time:</label>
            <input type="time" class="form-control" id="service_time" name="service_time" placeholder="Enter service time" value="{{ old('service_time') }}" required>
        </div>

        <div class="form-group">
            <label for="created_by">Created By:</label>
            <input type="text" class="form-control" id="created_by" name="created_by" placeholder="Enter creator name" value="{{ old('created_by') }}" required>
        </div>

        <div class="form-group">
            <label for="special">Special Instructions:</label>
            <input type="text" class="form-control" id="special" name="special" placeholder="Enter special instructions" value="{{ old('special') }}">
        </div>

        <div class="form-group">
            <label for="order_type">Order Type:</label>
            <input type="text" class="form-control" id="order_type" name="order_type" placeholder="Enter order type" value="{{ old('order_type') }}" required>
        </div>

        <div class="form-group">
            <label for="ship_region">Ship Region:</label>
            <input type="text" class="form-control" id="ship_region" name="ship_region" placeholder="Enter shipment region" value="{{ old('ship_region') }}" required>
        </div>

        <!-- Submit button -->
        <button type="submit" class="btn btn-primary">Create Shipment</button>
    </form>
</div>
@endsection --}}


@extends('core/base::layouts.master')

@section('content')
<div class="container">
    <h1>Create Shipment</h1>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    {!! form_start($form) !!}

    <form action="{{ route('eliteshipment.store') }}" method="POST">
        @csrf

        <!-- Fields from the form setup -->
        @foreach($form->getFields() as $field)
            <div class="form-group">
                <!-- Automatically render label and input -->
                {!! $field->render() !!}
            </div>
        @endforeach

        <!-- Submit button -->
        <button type="submit" class="btn btn-primary">Create Shipment</button>
    </form>
</div>
@endsection
