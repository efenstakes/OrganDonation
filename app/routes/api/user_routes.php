<?php
  /**
    keep the user routing code .ex
    /user, /user[/] , /user/[..] 
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


  // check if a user has a session 
  $app->post("/api/user/has-session[/]", function($request, $response, $args){
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

  // check if a user exists by id
  $app->post("/api/user/exists[/]", function($request, $response, $args){
    $return = array('exists'=> false);
    $id = $request->getParam('id');

    $exists = (new Staff())->setID($id)->exists();

    return json_encode($return);
  });

  // check if a user name is used
  $app->post("/api/user/name-used[/]", function($request, $response, $args){
    $name = $request->getParam('name');

    $name_used = (new User())->setName($name)->nameUsed();

    return json_encode(array('name_used'=> $name_used));
  });

  // check if a user account exists
  $app->post("/api/user/account-exists[/]", function($request, $response, $args){
    $return = array('exists'=> false);

    $name = $request->getParam('name');
    $password = $request->getParam('password');

    // return json_encode(array( 'name'=> $name, 'password'=> $password ));

    $return['exists'] = (new User())->setName($name)
                                     ->setPassword($password)
                                     ->accountExists();
   
    # var_dump(array('got name'=> $name, 'got password'=> $password));
    $id = (new User())->setName($name)->findID();
    $details = (new User())->setID($id)->setProperties()->getProperties();

    $_SESSION['app_session']['user_id'] = $id;
    $_SESSION['app_session']['user_type'] = 'user';
   
    # var_dump($return);

    $return['staff_data'] = $details;

    return json_encode($return);
  });

 // get details of a patient given their id
  $app->map(['GET', 'POST'], "/api/user/session-data[/]", function($request, $response, $args){
    $id = get_session()['app_session']['user_id'];

    $details = (new User())->setID($id)
                            ->setProperties()
                            ->getProperties();

    return json_encode(array('staff_data'=> $details));
  });

  // get details of a user given their id
  $app->map(['GET', 'POST'], "/api/user/details/{id}[/]", function($request, $response, $args){
    $id = $args['id'];

    $details = (new User())->setID($id)->setProperties()->getProperties();

    return json_encode(array('user'=> $details));
  });

  // @not-worked
  // delete a user given their id
  $app->post("/api/staff/delete/{id}[/]", function($request, $response, $args){
  	$return = array('deleted'=> false);
    $user = new User();
    $user_id = $args['id'];
    
    if(has_session()){
        $user_session_id = get_session()['app_session']['user_id'];

        $user_details = $user->setID($user_id)->setProperties()->getProperties(); 
        $user_session_details = $user->setID($user_session_id)->setProperties()->getProperties(); 

        if($user_details['id'] == $user_session_details['id']){
            $user->setID($staff_id)->delete();
            $return['deleted'] = true;
        }else if( $user_session_details['type'] == 'ADMIN' ){
            $user->setID($user_id)->delete();
            $return['deleted'] = true;
        }else{
            $return['deleted'] = false;
        }
  
    }

    return json_encode($return);

  });


  // this authenticates user before they are taken to their account page
  $app->post("/api/user/signin[/]", function($request, $response, $args){
    $return = array('authenticated'=> false);

    $name = trim($request->getParam("name"));
    $password = trim($request->getParam("password"));
        
    $user = new User();
    $exists = $user->setName($name)->setPassword($password)->accountExists();

    if($exists){ 
        $_SESSION['app_session']['user_id'] = $user->findID();
        $_SESSION['app_session']['user_type'] = 'user';
        $return['authenticated'] = true;
    }
           
    return json_encode($return);      
  });
  
    
  // log a user user out
  $app->map(['POST', 'GET'], "/api/user/logout[/]", function($request, $response, $args){
    $return = array('loged_out'=> false);

    delete_session();
    $return['loged_out'] = true;

    return json_encode($return);
  });
    



?>