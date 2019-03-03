<?php
  /**
    keep the user routing code .ex
    /organ-request, /organ-request[/] , /organ-request/[..] 
  **/

  
  // get the details that the user entered in the form
  // start saving organ request data to the db and return an array
  // array( 'saved'=> boolean() )
  $app->post("/api/organ-request/save[/]", function($request, $response, $args){
    $return = array('saved'=> false, 'id'=> NULL, 'errors'=> array());

    $name = trim($request->getParam('name'));
    $pwd = trim($request->getParam('password'));
    $confirmation_password = trim($request->getParam('confirmation_password'));

    $type = trim($request->getParam('type'));
    $email = trim($request->getParam('email'));
    $institution_id = trim($request->getParam('institution_id'));

    $ref = Utility::getAlphanumerics();
  
   
    $new_user = new User();
    $new_user->setName($name)
             ->setPassword($pwd)
             ->setInstitution(array(
                'id' => $institution_id
              ))
             ->setType($type)
             ->setContacts(array(
                'email' => $email
             ));


    if( $pwd === $confirmation_password && !$new_staff->nameUsed() ){
        $return = $new_user->save();
    
        //return json_encode(array('got_here'=> true));
               
        // set session
        $_SESSION['app_session']['user_id'] = $return['id'];
        $_SESSION['app_session']['user_type'] = 'staff';
        $_SESSION['app_session']['is_new'] = true;

    }

    return json_encode($return);
    
  });


  // accept organ request  
  $app->post("/api/organ-request/sort[/]", function($request, $response, $args){
    $return = array('has_session'=> false);

    if( !empty($_SESSION['app_session']['user_type']) ){
      $return['has_session'] = true;
      $return['session_key'] = $_SESSION['user_id'];
    }else{
      $return['has_session'] = false;
      $return['session_key'] = NULL;
    }

    return json_encode($return);
  });



  // get details of an organ request given their id
  $app->get("/api/organ-request/{id}/details[/]", function($request, $response, $args){
    $id = $args['id'];

    $details = (new OrganRequest())->setID($id)->setProperties()->getProperties();

    return json_encode(array('organ_request'=> $details));
  });


?>