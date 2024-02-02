<?php
     session_start();
     include_once'database/conn.inc.php';
        if($_SESSION['type'] != 'Manager'){
    
            header('location:page-login.php');
}
?><!DOCTYPE html>
<html class="h-100" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>ANGELO HARDWARE</title>
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="../../assets/images/favicon.png">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">
    <link href="css/style.css" rel="stylesheet">
    <link href="./plugins/pg-calendar/css/pignose.calendar.min.css" rel="stylesheet">
    <!-- Chartist -->
    <link rel="stylesheet" href="./plugins/chartist/css/chartist.min.css">
    <link rel="stylesheet" href="./plugins/chartist-plugin-tooltips/css/chartist-plugin-tooltip.css">
    <link rel="stylesheet" href="./plugins/tables/css/datatable/dataTables.bootstrap4.min.css">
    <!-- Custom Stylesheet -->
    <link href="css/style.css" rel="stylesheet">
    <link href="./plugins/tables/css/datatable/dataTables.bootstrap4.min.css" rel="stylesheet">
    
    
</head>

<body class="h-180">
  <div id="main-wrapper">  
    <!--*******************
        Preloader start
    ********************-->
   
    <!--*******************
        Preloader end
    ********************-->

    


          <!--**********************************
                    Header start
          ***********************************-->
   
  
    <div class="header">    
            <div class="header-content clearfix">           
                <div class="header-right">
                    <div class="input-group icons">
                         <button class="btn login-form__btn submit w-10 float-left" name="add" onclick="openModal('vendite')">Add Product</button>

                         &nbsp;
                          <button class="btn login-form__btn submit w-10 float-left" name="add" onclick="location.href='index.php';">Exit </button>
                         &nbsp;
                       </div>
               </div>
              </div>
      </div>
                      
                <br>                  
        
        
        <!--**********************************
            Header end 
        ***********************************-->
 
   
    <div class="login-form-bg h-150">
        <div class="container h-150">
            <div class="row justify-content-center h-150">
                <div class="col-md-12">
                    <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Product Details</h4>
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered display table-sm items-table" id="myTable">
                                      <thead>
                                          <tr>
                                            <th>#</th>
                                            <th>Product Name</th>
                                            <th>Category</th>
                                            <th>Buying Price</th>
                                            <th>Selling Price</th>
                                            <th>Quantity</th>
                                            <th>Action</th>
                                            <th>Action</th>
                                          </tr>
                                        </thead>
                                        <tbody></tbody>
                                        <tfoot>
                                          <tr>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th>Total Buying Price:</th>
                                            <th>Total Selling Price:</th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                          </tr>
                                        </tfoot>
                                      </table>
                                </div>
                            </div>
                        </div>
                   
                </div>
                    
                </div>
            </div>
                            
            
                
                
                
                  <!-- Modal -->
<div class="modal fade" id="update-product-modal" tabindex="-1" role="dialog" aria-labelledby="updateProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateProductModalLabel">Update Product</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="update-product-form">
                    <input type="hidden" id="product-id-input" name="product-id-input">
                    <div class="form-row">
                      <div class="form-group col-md-4">
                        <label for="product-name-input">Product Name</label>
                        <input type="text" class="form-control" id="product-name-input" name="product-name-input">
                      </div>
                    <div class="form-group col-md-4">
                        <label for="company-input">Category</label>
                        <input type="text" name="category" id="categorie" class="form-control" required>
                     </div>
                    <div class="form-group col-md-4">
                        <label for="buying-price-input">Buying Price</label>
                        <input type="number" class="form-control" id="buying-price-input" name="buying-price-input">
                    </div>
                  </div>
                   <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="selling-price-input">Selling Price</label>
                        <input type="number" class="form-control" id="selling-price-input" name="selling-price-input">
                    </div>
                    
                    <div class="form-group col-md-4">
                        <label for="shop-quantity-input">Available Quantity</label>
                        <input type="number" class="form-control" id="shop-quantity-input" name="shop-quantity-input" disabled>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="shop-quantity-input">Quantity Received</label>
                        <input type="number" class="form-control" id="new-quantity-input" name="new-quantity-input">
                    </div>
                  </div>
                    <button type="submit" class="btn btn-primary">Update Product</button>
                </form>
            </div>
        </div>
    </div>
</div>

                
                
                <!-- add new products modal -->
                 <div class="modal fade" id="vendite">
                  <div class="modal-dialog" role="document">
                    <div class="modal-content">
                       <div class="dropdown-content-body-sm">
                                    <div class="card">
                                       <div class="card-body">
                                                               
                                <p></p>
                                <h4 class="card-title">Add New Product</h4>
                                <p></p>
                                
                                <div></div>
                                <div class="basic-form">
                                    <form  id="product-form">
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label>Product Name</label>
                                                <input type="text" class="form-control" placeholder="Product Name" id="product_name" name="product_name" required>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Buying Price</label>
                                                <input type="number" class="form-control" placeholder="Buying Price"   id="buying_price" name="buying_price" required>
                                            </div>
                                           </div>
                                           <div class="form-row">
                                            
                                            <div class="form-group col-md-6">
                                                <label>Category</label>
                                                <input type="text" class="form-control" placeholder="Category"   id="category"  name="category" required>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Sellling Price</label>
                                                <input type="number" class="form-control" placeholder="Selling Price"  id="selling_price" name="selling_price"required>
                                            </div>
                                        </div>
                                        <div class="form-row">
                                            
                                                
                                          
                                            <div class="form-group col-md-6">
                                                <label>Quantity</label>
                                                <input type="number" class="form-control" placeholder="Quantity" id="shop_quantity" name="quantity" required>
                                                <input type="hidden" class="form-control" placeholder="Quantity" id="store_quantity" name="store_quantity"  value="0" >
                                            </div>
                                           
                                            
                                        </div>
                                         
                                        <button type="submit" class="btn btn-dark">Submit</button>
                                         <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                    </form>
                                </div>
                            </div>
                          </div>
                       </div>
                    </div>
                  </div>
                </div>
    </div>
    </div>
       
    

    

    <!--**********************************
        Scripts
    ***********************************-->
        <script src="plugins/common/common.min.js"></script>
    <script src="js/custom.min.js"></script>
    <script src="js/settings.js"></script>
    <script src="js/gleek.js"></script>
    <script src="js/styleSwitcher.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

   
    <!-- Circle progress -->
    <script src="./plugins/circle-progress/circle-progress.min.js"></script>
    <!-- Datamap -->
    <script src="./plugins/d3v3/index.js"></script>
    <script src="./plugins/topojson/topojson.min.js"></script>
    <script src="./plugins/datamaps/datamaps.world.min.js"></script>
    <!-- Morrisjs -->
    <script src="./plugins/raphael/raphael.min.js"></script>
    <script src="./plugins/morris/morris.min.js"></script>
    <!-- Pignose Calender -->
    <script src="./plugins/moment/moment.min.js"></script>
    <script src="./plugins/pg-calendar/js/pignose.calendar.min.js"></script>
    <!-- ChartistJS -->
    <script src="./plugins/chartist/js/chartist.min.js"></script>
    <script src="./plugins/chartist-plugin-tooltips/js/chartist-plugin-tooltip.min.js"></script>
    <!--Data-TableJs-->
    <script src="./plugins/tables/js/jquery.dataTables.min.js"></script>
    <script src="./plugins/tables/js/datatable/dataTables.bootstrap4.min.js"></script>
    <script src="./plugins/tables/js/datatable-init/datatable-basic.min.js"></script>
    
    

    <script src="./js/dashboard/dashboard-1.js"></script>
     <script src="./plugins/tables/js/jquery.dataTables.min.js"></script>
    <script src="./plugins/tables/js/datatable/dataTables.bootstrap4.min.js"></script>
    <script src="./plugins/tables/js/datatable-init/datatable-basic.min.js"></script>
    
    <script>
  function openModal(modalId) {
    $('#' + modalId).modal('show');
  }
</script>
    
     <script>
 //Add Stock Modal 
 $('#addstock').click(function(){
   $('#basicModal').modal();
 });
//Send stock to Shop  
 $('#sendshop').click(function(){
   $('#ShopModal').modal();
 });
// Items To Send
  $('#storecart').click(function(){
    $('#shopite').modal();
  });    
// Add Vendor
  $('#vendor').click(function(){
    $('#vendite').modal();
  });    
 </script>

    <script>
		$(document).ready(function() {
			// Add event listener to form submit button
			$('#product-form').on('submit', function(event) {
				event.preventDefault(); // Prevent default form submission behavior

				// Serialize form data
				var formData = $(this).serialize();

				// Send AJAX request to server
				$.ajax({
					url: 'database/products.kts.php', // PHP script to handle the request
					type: 'POST',
					data: formData,
					success: function(response) {
                        // Display the response from the PHP script
                        alert(response);

                        // Clear the form input fields
                        $('#product-form')[0].reset();
                      },
					error: function(xhr, status, error) {
						// Handle errors
						console.log(xhr.responseText);
					}
				});
			});
		});
	</script>
    
    <script>
$(document).ready(function () {
  var table = $('#myTable').DataTable({
    ajax: {
      url: 'database/get_products.kts.php',
      type: 'GET',
      dataSrc: ''
    },
    columns: [
      { data: 'product_id' },
      { data: 'product_name' },
      { data: 'category' },
      { data: 'buying_price' },
      { data: 'selling_price' },
      { data: 'quantity' },
      {
        // Update button column
        render: function(data, type, row) {
          return '<button class="btn btn-primary update-product" data-product-id="' + row.product_id + '">Update</button>';
        }
      },
      {
        // Delete button column
        render: function(data, type, row) {
          return '<button class="btn btn-danger delete-product" data-product-id="' + row.product_id + '">Delete</button>';
        }
      }
    ],
    dom: 'Bfrtip',
    buttons: [
      {
        extend: 'pdfHtml5',
        text: 'Export to PDF',
        exportOptions: {
          columns: ':not(.no-export)'
        }
      }
    ],
    footerCallback: function(row, data, start, end, display) {
      var buyingPriceTotal = 0;
      var sellingPriceTotal = 0;

      // Calculate the total 'buying_price * quantity' and 'selling_price * quantity' for each product
      this.api().rows().every(function () {
        var data = this.data();
        buyingPriceTotal += parseFloat(data.buying_price) * parseFloat(data.quantity);
        sellingPriceTotal += parseFloat(data.selling_price) * parseFloat(data.quantity);
      });

      // Update the footer with totals
      $(this.api().column(3).footer()).html('Total Buying Price: ' + buyingPriceTotal.toFixed(2));
      $(this.api().column(4).footer()).html('Total Selling Price: ' + sellingPriceTotal.toFixed(2));
    }
  });
});

  // Update button click handler
  $('.items-table tbody').on('click', '.update-product', function() {
    var productId = $(this).data('product-id');
   
    // Make an AJAX call to retrieve the current product details
    $.ajax({
      url: './database/get_products.kts.php',
      type: 'POST',
      data: { 'product_id': productId },
      success: function(response) {
        // Parse the response JSON and populate the modal fields
          var products = JSON.parse(response);
        $('#product-id-input').val(products.product_id);
        $('#product-name-input').val(products.product_name);
        $('#categorie').val(products.category);
        $('#buying-price-input').val(products.buying_price);
        $('#selling-price-input').val(products.selling_price);
        $('#shop-quantity-input').val(products.quantity);
        // Display the modal
        $('#update-product-modal').modal('show');
      },
      error: function(jqXHR, textStatus, errorThrown) {
        console.error(errorThrown);
      }
    });
  });

  // Delete button click handler
  $('.items-table tbody').on('click', '.delete-product', function() {
    var productId = $(this).data('product-id');
    // Make an AJAX call to delete the product from the server
    $.ajax({
      url: './database/delete_product.kts.php',
      type: 'POST',
      data: { 'product_id': productId },
      success: function(response) {
        // Refresh the table data
        table.ajax.reload();
      },
      error: function(jqXHR, textStatus, errorThrown) {
        console.error(errorThrown);
      }
    });
  });
});

</script>

 <script>
		$(document).ready(function() {
			// Add event listener to form submit button
			$('#update-product-form').on('submit', function(event) {
				event.preventDefault(); // Prevent default form submission behavior
              
             

				// Serialize form data
				var formData = $(this).serialize();
                
               

				// Send AJAX request to server
				$.ajax({
					url: './database/updateproduct.kts.php', // PHP script to handle the request
					type: 'POST',
					data: formData,
					success: function(response) {
                        // Display the response from the PHP script
                        alert(response);

                        // Clear the form input fields
                        $('#update-product-form')[0].reset();
                        location.reload();
                      },
					error: function(xhr, status, error) {
						// Handle errors
						console.log(xhr.responseText);
					}
				});
			});
		});
	</script>
	
</body>
</html>





