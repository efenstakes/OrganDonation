<?php
  /**
    keep the patient routing code .ex
    /patients, /patient[/] , /patient/[..] 
  **/
    /*
       'name': this.name,
             'password': this.password,
             'confirmation_password': this.password_confirmation,
             'city': this.city,
             'email': this.email,
             'gender': this.gender,
             'dob': this.dob,
             'blood_type': this.blood_type,
             'user_type': this.user_type
             */
  
  // get the details that the user entered in the form
  // start saving user data to the db and return an array
  // array( 'added'=> boolean(), 'user'=> (new User()) )
  $app->post("/api/patient/save[/]", function($request, $response, $args){
    $return = array('saved'=> false, 'id'=> NULL, 'errors'=> array());

    $name = trim($request->getParam('name'));
    $pwd = trim($request->getParam('password'));
    $confirmation_password = trim($request->getParam('confirmation_password'));

    $city = trim($request->getParam('city'));
    $gender = trim($request->getParam('gender'));
    $email = trim($request->getParam('email'));
    $dob = trim($request->getParam('dob'));
    $blood_type = trim($request->getParam('blood_type'));
    $user_type = trim($request->getParam('user_type'));

   
    $new_patient = new Patient();
    $new_patient->setName($name)
             ->setPassword($pwd)
             ->setLocation(array(
                'city' => $city
              ))
             ->setGender($gender)
             ->setContacts(array(
                'email' => $email
             ))
             ->setDob($dob)
             ->setBloodType($blood_type)
             ->setType($user_type);

   

    if( $pwd === $confirmation_password && !$new_patient->nameUsed() ){
        $return = $new_patient->save();
    
        //return json_encode(array('got_here'=> true));
               
        // set session
        $_SESSION['app_session']['user_id'] = $return['id'];
        $_SESSION['app_session']['is_new'] = true;
        $_SESSION['app_session']['user_type'] = $user_type;

    }

    return json_encode($return);
    
  });


  // check if a patient has a session 
  $app->post("/api/patient/has-session[/]", function($request, $response, $args){
    $return = array('has_session'=> false);

    $return['has_session'] = has_session();

    return json_encode($return);
  });

  // check if a patient exists by id
  $app->post("/api/patient/exists[/]", function($request, $response, $args){
    $return = array('exists'=> false);
    $id = $request->getParam('id');

    $exists = (new Patient())->setID($id)->exists();

    return json_encode($return);
  });

  // check if a patient exists by id
  $app->post("/api/patient/all[/]", function($request, $response, $args){
    $all = array();
    $all = (new Patient())->getAll();
    return json_encode($all);
  });

  // check if a patient name is used
  $app->post("/api/patient/name-used[/]", function($request, $response, $args){
    $name = $request->getParam('name');

    $name_used = (new Patient())->setName($name)->nameUsed();

    return json_encode(array('name_used'=> $name_used));
  });

  // check if a patient account exists
  $app->post("/api/patient/account-exists[/]", function($request, $response, $args){
    $return = array('exists'=> false);

    $name = $request->getParam('name');
    $password = $request->getParam('password');

    $return['exists'] = (new Patient())->setName($name)->setPassword($password)
                                  ->accountExists();

    return json_encode($return);
  });


  // get details of a patient given their id
  $app->map(['GET', 'POST'], "/api/patient/session-data[/]", function($request, $response, $args){
    $id = get_session()['app_session']['user_id'];

    $details = (new Patient())->setID($id)->setProperties()->getProperties();

    return json_encode(array('patient'=> $details));
  });

  // get details of a patient given their id
  $app->map(['GET', 'POST'], "/api/patient/details/{id}[/]", function($request, $response, $args){
    $id = $args['id'];

    $details = (new Patient())->setID($id)->setProperties()->getProperties();

    return json_encode(array('patient'=> $details));
  });

  // @not-worked
  // delete a patient given their id
  $app->post("/api/patient/delete/{id}[/]", function($request, $response, $args){
  	$return = array('deleted'=> false);
    $patient = new Patient();
    $patient_id = $args['id'];
    
    if(has_session()){
        $patient_session_id = get_session()['hmp_session']['patient_id'];

        $patient_details = $patient->setID($patient_id)->setProperties()->getProperties(); 
        $patient_session_details = $patient->setID($patient_session_id)->setProperties()->getProperties(); 

        if($patient_details['id'] == $patient_session_details['id']){
            $patient->setID($patient_id)->delete();
            $return['deleted'] = true;
        }else if( $patient_session_details['type'] == 'ADMIN' ){
            $patient->setID($patient_id)->delete();
            $return['deleted'] = true;
        }else{
            $return['deleted'] = false;
        }
  
    }

    return json_encode($return);

  });


  // this authenticates patient before they are taken to their account page
  $app->post("/api/patient/signin[/]", function($request, $response, $args){
    $return = array('authenticated'=> false);

    $name = trim($request->getParam("name"));
    $password = trim($request->getParam("password"));
        
    $patient = new Patient();
    $exists = $patient->setName($name)->setPassword($password)->accountExists();

    if($exists){ 
        $_SESSION['app_session']['user_id'] = $patient->findID();
        $_SESSION['app_session']['user_type'] ='patient';
        $return['authenticated'] = true;
        $return['patient'] = $patient->setID($patient->findID())
                                     ->setProperties()
                                     ->getProperties();
    }
           
    return json_encode($return);      
  });
  
    
  // log a user out
  $app->map(['POST', 'GET'], "/api/patient/logout[/]", function($request, $response, $args){
    $return = array('loged_out'=> false);

    delete_session();
    $return['loged_out'] = true;

    return json_encode($return);
  });




?>