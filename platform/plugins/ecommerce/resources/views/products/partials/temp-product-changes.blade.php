{{-- 
@extends($layout ?? BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
<form action="{{ route('temp-products.approve') }}" method="POST">
    @csrf
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    
                    <th>Product ID</th>
                    <th>Product Name</th>
                    <th>Change Description</th>
                    <th>Current Status</th>
                    <th>Approval Status</th>
                    <th>Edit</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($tempProducts as $tempProduct)
                    <tr>
                      
                        <td>{{ $tempProduct->product_id }}</td>
                        <td>{{ $tempProduct->name }}</td>
                        <td>{{ $tempProduct->description }}</td>
                        <td>{{ $tempProduct->status }}</td>
                        <td>
                            <select name="approval_status[{{ $tempProduct->id }}]" class="form-control approval-status-dropdown">
                                <option value="pending" {{ $tempProduct->approval_status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ $tempProduct->approval_status == 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="rejected" {{ $tempProduct->approval_status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </sewlect>
                            
                        </td>
                        <td>
                            <a href="#" class="edit-temp-product" data-id="{{ $tempProduct->id }}" data-name="{{ $tempProduct->name }}" data-description="{{ $tempProduct->description }}" data-status="{{ $tempProduct->status }}">
                                <i class="fa fa-pencil"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <button type="submit" class="btn btn-success" id="save-changes-btn">Save Approval Changes</button>
</form>
@endsection --}}


















@extends($layout ?? BaseHelper::getAdminMasterLayoutTemplate())

@section('content')

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Temp Products</title>

    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS (Optional) -->
    <style>
        .edit-icon {
            cursor: pointer;
            font-size: 18px;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <form action="{{ route('temp-products.approve') }}" method="POST">
        @csrf
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Product ID</th>
                        <th>Product Name</th>
                        <th>Change Description</th>
                        <th>Current Status</th>
                        <th>Approval Status</th>
                        <th>Edit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tempProducts as $tempProduct)
                        <tr id="product-row-{{ $tempProduct->id }}">
                            <td>{{ $tempProduct->product_id }}</td>
                            <td class="product-name">{{ $tempProduct->name }}</td>
                            <td class="product-description">{{ $tempProduct->description }}</td>
                            <td class="product-status">{{ $tempProduct->status }}</td>
                            <td>
                                <select name="approval_status[{{ $tempProduct->id }}]" class="form-control approval-status-dropdown">
                                    <option value="pending" {{ $tempProduct->approval_status == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="approved" {{ $tempProduct->approval_status == 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="rejected" {{ $tempProduct->approval_status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                            </td>
                            <td>
                                <button type="button" class="edit-icon" data-toggle="modal" data-target="#editProductModal" 
                                   data-id="{{ $tempProduct->id }}"
                                   data-name="{{ $tempProduct->name }}"
                                   data-description="{{ $tempProduct->description }}"
                                   data-content="{{ $tempProduct->content }}"
                                   data-status="{{ $tempProduct->status }}"
                                   data-approval-status="{{ $tempProduct->approval_status }}">
                                   <i class="fas fa-pencil-alt"></i> <!-- Pencil icon -->
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <button type="submit" class="btn btn-success" id="save-changes-btn">Save Approval Changes</button>
    </form>
</div>

<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1" role="dialog" aria-labelledby="editProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProductModalLabel">Edit Product</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('temp-products.approve') }}" method="POST">
                    @csrf
                    <div class="products-container">
                        @foreach ($tempProducts as $tempProduct)
                            <div class="product-card" id="product-row-{{ $tempProduct->id }}">
                                <div class="product-header">
                                    <h6>Product ID: {{ $tempProduct->product_id }}</h6>
                                    <h4>{{ $tempProduct->name }}</h4>
                                </div>
                               
                                <div class="product-description">
                                    <label for="description-{{ $tempProduct->id }}">Change Description:</label>
                                    <textarea id="description-{{ $tempProduct->id }}" class="editor" name="description[{{ $tempProduct->id }}]">
                                        {{ $tempProduct->description }}
                                    </textarea>
                                </div>
                                <div class="product-content">
                                    <label for="content-{{ $tempProduct->id }}">Change Content:</label>
                                    <textarea id="description-{{ $tempProduct->id }}" class="editor" name="content[{{ $tempProduct->id }}]">
                                        {{ $tempProduct->content }}
                                    </textarea>
                                </div>
                                <div class="approval-status-container">
                                    <label for="approval-status-{{ $tempProduct->id }}">Approval Status:</label>
                                    <select name="approval_status[{{ $tempProduct->id }}]" id="approval-status-{{ $tempProduct->id }}" class="form-control approval-status-dropdown">
                                        <option value="pending" {{ $tempProduct->approval_status == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="approved" {{ $tempProduct->approval_status == 'approved' ? 'selected' : '' }}>Approved</option>
                                        <option value="rejected" {{ $tempProduct->approval_status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                    </select>
                                </div>
                                <div class="edit-button-container">
                                    <button type="button" class="edit-icon" data-toggle="modal" data-target="#editProductModal" 
                                        data-id="{{ $tempProduct->id }}"
                                        data-name="{{ $tempProduct->name }}"
                                        data-description="{{ $tempProduct->description }}"
                                         data-content="{{ $tempProduct->content }}"
                                        data-status="{{ $tempProduct->status }}"
                                        data-approval-status="{{ $tempProduct->approval_status }}">
                                        
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                
                    <button type="submit" class="btn btn-success" id="save-changes-btn">Save Approval Changes</button>
                </form>
                
                
            </div>
        </div>
    </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>


<!-- Bootstrap JS -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
<script>
    document.querySelectorAll('.editor').forEach((element) => {
        ClassicEditor
            .create(element)
            .catch((error) => {
                console.error(error);
            });
    });
</script>

<script>
    tinymce.init({
        selector: '.editor',
        menubar: false,
        toolbar: 'undo redo | styleselect | bold italic | alignleft aligncenter alignright | bullist numlist outdent indent | link image',
        plugins: 'lists link image',
        content_style: "body { font-family:Helvetica,Arial,sans-serif; font-size:14px }",
        setup: function (editor) {
            editor.on('change', function () {
                editor.save(); // This ensures that the data is saved into the textarea
            });
        }
    });
</script>


<style>
   
    .product-card {
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 15px;
        background-color: #f9f9f9;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .product-header {
        margin-bottom: 10px;
    }
    .product-status {
        margin: 10px 0;
        font-weight: bold;
    }
    .product-description ,   .product-content {
        margin-bottom: 10px;
    }
    .approval-status-container, .edit-button-container {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .editor {
        width: 100%;
        height: 150px; /* Set the height of the editor */
    }
</style>
<script>
    $(document).ready(function () {
        // Edit Product button click
        $(document).on('click', '.edit-icon', function () {
            var currentProductId = $(this).data('id');
            var productName = $(this).data('name');
            var productDescription = $(this).data('description');
            var productContent = $(this).data('content');
            var approvalStatus = $(this).data('approval-status');

            // Populate modal fields
            $('#edit-product-id').val(currentProductId);
            $('#product-name').val(productName);
            $('#product-description').val(productDescription);
            $('#product-content').val(productContent);
            $('#approval-status').val(approvalStatus);
        });
    });
</script>

</body>

@endsection








