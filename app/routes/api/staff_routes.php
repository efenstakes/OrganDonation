<?php
  /**
    keep the staff routing code .ex
    /staffs, /staff[/] , /staff/[..] 
  **/

  
  // get the details that the user entered in the form
  // start saving user data to the db and return an array
  // array( 'added'=> boolean(), 'user'=> (new User()) )
  $app->post("/api/staff/save[/]", function($request, $response, $args){
    $return = array('saved'=> false, 'id'=> NULL, 'errors'=> array());

    $name = trim($request->getParam('name'));
    $pwd = trim($request->getParam('password'));
    $confirmation_password = trim($request->getParam('confirmation_password'));

    $type = trim($request->getParam('type'));
    $email = trim($request->getParam('email'));
    $institution_id = trim($request->getParam('institution_id'));

    $ref = Utility::getAlphanumerics();
  
   
    $new_staff = new Staff();
    $new_staff->setName($name)
             ->setPassword($pwd)
             ->setInstitution(array(
                'id' => $institution_id
              ))
             ->setType($type)
             ->setContacts(array(
                'email' => $email
             ));


    if( $pwd === $confirmation_password && !$new_staff->nameUsed() ){
        $return = $new_staff->save();
    
        //return json_encode(array('got_here'=> true));
               
        // set session
        $_SESSION['app_session']['user_id'] = $return['id'];
        $_SESSION['app_session']['user_type'] = 'staff';
        $_SESSION['app_session']['is_new'] = true;

    }

    return json_encode($return);
    
  });


  // check if a staff has a session 
  $app->post("/api/staff/has-session[/]", function($request, $response, $args){
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

  // check if a staff exists by id
  $app->post("/api/staff/exists[/]", function($request, $response, $args){
    $return = array('exists'=> false);
    $id = $request->getParam('id');

    $exists = (new Staff())->setID($id)->exists();

    return json_encode($return);
  });

  // check if a staff name is used
  $app->post("/api/staff/name-used[/]", function($request, $response, $args){
    $name = $request->getParam('name');

    $name_used = (new Staff())->setName($name)->nameUsed();

    return json_encode(array('name_used'=> $name_used));
  });

  // check if a staff account exists
  $app->post("/api/staff/account-exists[/]", function($request, $response, $args){
    $return = array('exists'=> false);

    $name = $request->getParam('name');
    $password = $request->getParam('password');

    // return json_encode(array( 'name'=> $name, 'password'=> $password ));

    $return['exists'] = (new Staff())->setName($name)
                                     ->setPassword($password)
                                     ->accountExists();
   
    # var_dump(array('got name'=> $name, 'got password'=> $password));
    $id = (new Staff())->setName($name)->findID();
    $details = (new Staff())->setID($id)->setProperties()->getProperties();

    $_SESSION['app_session']['user_id'] = $id;
    $_SESSION['app_session']['user_type'] = 'staff';
   
    # var_dump($return);

    $return['staff_data'] = $details;

    return json_encode($return);
  });

 // get details of a patient given their id
  $app->map(['GET', 'POST'], "/api/staff/session-data[/]", function($request, $response, $args){
    $id = get_session()['app_session']['user_id'];

    $details = (new Staff())->setID($id)
                            ->setProperties()
                            ->getProperties();

    return json_encode(array('staff_data'=> $details));
  });

  // get details of a staff given their id
  $app->map(['GET', 'POST'], "/api/staff/details/{id}[/]", function($request, $response, $args){
    $id = $args['id'];

    $details = (new Staff())->setID($id)->setProperties()->getProperties();

    return json_encode(array('staff'=> $details));
  });

  // @not-worked
  // delete a staff given their id
  $app->post("/api/staff/delete/{id}[/]", function($request, $response, $args){
  	$return = array('deleted'=> false);
    $staff = new Staff();
    $staff_id = $args['id'];
    
    if(has_session()){
        $staff_session_id = get_session()['app_session']['user_id'];

        $staff_details = $staff->setID($staff_id)->setProperties()->getProperties(); 
        $staff_session_details = $staff->setID($staff_session_id)->setProperties()->getProperties(); 

        if($staff_details['id'] == $staff_session_details['id']){
            $staff->setID($staff_id)->delete();
            $return['deleted'] = true;
        }else if( $staff_session_details['type'] == 'ADMIN' ){
            $staff->setID($staff_id)->delete();
            $return['deleted'] = true;
        }else{
            $return['deleted'] = false;
        }
  
    }

    return json_encode($return);

  });


  // this authenticates staff before they are taken to their account page
  $app->post("/api/staff/signin[/]", function($request, $response, $args){
    $return = array('authenticated'=> false);

    $name = trim($request->getParam("name"));
    $password = trim($request->getParam("password"));
        
    $staff = new Staff();
    $exists = $staff->setName($name)->setPassword($password)->accountExists();

    if($exists){ 
        $_SESSION['app_session']['user_id'] = $staff->findID();
        $_SESSION['app_session']['user_type'] = 'staff';
        $return['authenticated'] = true;
    }
           
    return json_encode($return);      
  });
  
    
  // log a user out
  $app->map(['POST', 'GET'], "/api/staff/logout[/]", function($request, $response, $args){
    $return = array('loged_out'=> false);

    delete_session();
    $return['loged_out'] = true;

    return json_encode($return);
  });
    



?>