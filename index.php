<?php 
   /*
     @author nigel waweru
     @doc the application entry page
     @todo define routes and handle them 
   */

    // enable sessions
    session_start();
    
    // allow cross origin requests
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS'); 
    header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
    header("Access-Control-Allow-Headers: content-type, authorization");
    header("Access-Control-Allow-Credentials: true");

    // include php libraries downloaded
    require_once("./vendor/autoload.php");

    // import the packaes and classes to use 
    use Slim\Http\Request;
    use Slim\Http\Response;
    use Slim\Http\UploadedFile;

    require_once("hmp/models/Staff.php");
    require_once("hmp/models/Patient.php");
    require_once("hmp/models/Utility.php");

    // setup slim framework for routing
    $app = new Slim\App([
           'settings' => [ 'displayErrorDetails' => true, 'debug' => true, ]
    ]);

    $container = $app->getContainer();

    // add view to the container
    // using slim template lang
    $container['view'] = function($cont){
        $template_dir = __DIR__ . "/hmp/views/";
        $cache = false;
        
        return new Slim\Views\Twig($template_dir, compact('cache')); 
    };


    // start handling routes
    include_once("./hmp/routes/api/staff_routes.php");
    include_once("./hmp/routes/api/patient_routes.php");



    
    // check if a session exists 
    function has_session(){
       return (!empty($_SESSION['app_session']) && !empty($_SESSION['app_session']['user_id']));
    }// end of has_session() 
    
    // return the session
    function get_session(){
       return $_SESSION;
    }// end of get_session()
    
    /* 
      check if a session is valid
      validity is determined by existence of a session and.. 
      a host whose data coincides with the session
    **/
    function is_session_valid(){
      $return = false;
      
      if(has_session()){
         $user_id = get_session()['app_session']['user_id'];
         $user_type = get_session()['app_session']['user_type'];
         $return = true; // return exists status for the entity 
      }
      return $return;
    }
    
    // delete a session if it exists
    function delete_session(){
        unset($_SESSION['app_session']);
    }// end of delete_session()
    
    // checks if a session exists and return any data pertaining to it
    function get_and_return_data(){
      $user_data = array('host'=> 'NOT SET');
      if(has_session()){
        $user_id = get_session()['app_session']['user_id'];
        $user_data['user'] = true; // (new Host())->setID($user_id)->setProperties()->getProperties();
      }
      return $user_data;
    }// end of get_and_return_data() 

    // moves an upload file to the passed directory
    function moveUploadedFile($directory, UploadedFile $uploadedFile){
      $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
      $basename = bin2hex(random_bytes(8)); // see http://php.net/manual/en/function.random-bytes.php
      $filename = sprintf('%s.%0.8s', $basename, $extension);

      $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

      return $filename;
    }// end of get_and_return_data() 


    // run the app
    $app->run();

?>