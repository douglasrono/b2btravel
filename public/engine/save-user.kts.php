 <?php 
 session_start();
 require_once '../vendor/autoload.php';
 require_once 'shikanisha.kts.php';

 $client_id ="GOOGLE_CLIENT_ID";
 $id_token = $_POST['response'];
 