<?php
   
  include_once("User.php");
  include_once("DbManager.php");


    
  /*
    contain data that has to do with a user.. 
    acts as the superclass for user-related classes
  */
  class Patient extends User {
    private $blood_type = NULL;
    private $db_handle = NULL;

    // set the default values for the object variables
    public function __construct($name = "", $id = "", $password = ""){
        #super::__construct($name, $id, $password);

        $db_manager = new DbManager();
        $this->db_handle = $db_manager->getHandle();
    }  

    // 
    public function __toString(){
       return "id :". $this->getID() ." , name :". $this->getName();
    } 


    public function setBloodType($type) {
      $this->blood_type = $type;
      return $this;
    } // public function setBloodtype($type) { .. }

    public function getBloodType() {
      return $this->blood_type;
    } // public function getBloodtype() { .. }







    // save a photo instance
    public function save(){
      $save_result = array('saved'=> false, 'id'=> NULL);

      try{
        $this->db_handle->beginTransaction();

        $query = "insert into patients " 
                  ." ( name, password, city, email, gender, dob, user_type, blood_type ) "
                  ." values(?, ?, ?, ?, ?, ?, ?, ?)";
 
        $query_data = array(
                        $this->getName(),
                        $this->getPassword(),
                        $this->getLocation()['city'],
                        $this->getContacts()['email'],
                        $this->getGender(),
                        $this->getDob(),
                        $this->getType(),
                        $this->getBloodType()
                      );

        $stmt = $this->db_handle->prepare($query);
        $stmt->execute($query_data);
        
        $id = $this->db_handle->lastInsertId();

        if($stmt->rowCount() > 0){
           $save_result['id'] = $id;
           $save_result['saved'] = true;
        }

        $this->db_handle->commit();
      }catch(PDOException $e){
        echo($e->getMessage());
        $this->db_handle->rollBack();
      }

       return $save_result;
    }// end of public function save(){ .. {}
      

    // check if a patient exists
    public function findID(){
      $name = NULL;
      $query = "select * from patients where name = ?";

      $stmt = $this->db_handle->prepare($query);
      $stmt->execute(array( $this->getName() ));

      $details = $stmt->fetchAll(PDO::FETCH_ASSOC);

      if( $stmt->rowCount() > 0 ){
        $name = $details[0]['id'];
      }

      return $name;
    }// public function exists(){ .. }


    // check if a patient exists
    public function nameUsed(){
      $exists = false;
      $query = "select * from patients where name = ?";

      $stmt = $this->db_handle->prepare($query);
      $stmt->execute(array( $this->getName() ));

      if( $stmt->rowCount() > 0 ){
         $exists = true;
      }

      return $exists;
    }// public function exists(){ .. }

    // check if a patient exists
    public function accountExists(){
      $exists = false;
      $query = "select * from patients where password = ? AND name = ?";

      $stmt = $this->db_handle->prepare($query);
      $stmt->execute(array( $this->getPassword(), $this->getName() ));

      if( $stmt->rowCount() > 0 ){
         $exists = true;
      }

      return $exists;
    }// public function exists(){ .. }

    // check if a patient exists
    public function exists(){
      $exists = false;
      $query = "select * from patients where id = ?";

      $stmt = $this->db_handle->prepare($query);
      $stmt->execute(array( $this->getID() ));

      if( $stmt->rowCount() > 0 ){
         $exists = true;
      }

      return $exists;
    }// public function exists(){ .. }

    // delete a patient given their id 
    public function delete(){
       $query = "delete from patients where id = ?";
       $stmt = $this->db_handle->prepare($query);
       $stmt->execute(array( $this->getID() ));
    }// end of public function delete(){ .. }




    public function setProperties(){
      $patient = new Patient();
      try{

        //get primary photo details like name etc
        $primary_query = "select * from patients where id = ?";
        $primary_stmt = $this->db_handle->prepare($primary_query);
        $primary_stmt->execute(array( $this->getID() ));
        $primary_results = $primary_stmt->fetch(PDO::FETCH_ASSOC);

        //get primary photo details like name etc
        /*
        $titles_query = "select specialty from specialty where STAFFID = ?";
        $titles_stmt = $this->db_handle->prepare($titles_query);
        $titles_stmt->execute(array($this->getID()));
        $titles_results = $titles_stmt->fetch(PDO::FETCH_ASSOC);
        */

        // combined properties
        $combined_properties = array( 'patient'=> $primary_results );

        $patient = Patient::make($combined_properties);

        
        # $this->db_handle->commit();
      }catch(PDOException $e){
        echo($e->getMessage());
      }

      return $patient;
    }

    // make the details of a photo
    // database data is passed in an array then this method returns a photo instance
    public static function make($patient_properties){
      $this_patient = new Patient();

      // unpack the properties from the photo_properties array
      $patient = $patient_properties['patient'];

      $this_patient->setID($patient['id'])
                 ->setPassword($patient['password'])
                 ->setName($patient['name'])
                 ->setGender($patient['gender'])
                 ->setContacts(array( 'email' => $patient['email'] ))
                 ->setLocation(array( 'city' => $patient['city'] ))
                 ->setDob($patient['dob'])
                 ->setType($patient['user_type'])
                 ->setBloodtype($patient['blood_type']);

      return $this_patient;
    } // end of public static function make($photo_properties){ .. }

    // return an array with the photo details
    public function getProperties(){
      $properties = array();
      
      $properties['id'] = $this->getID();
      $properties['name'] = $this->getName();
      $properties['location'] = $this->getLocation();
      $properties['contacts'] = $this->getContacts();
      $properties['gender'] = $this->getGender();
      $properties['dob'] = $this->getDob();
      $properties['user_type'] = $this->getType();
      $properties['blood_type'] = $this->getBloodType();

      return $properties;
    }// end of public function getProperties(){ .. }



    public function getAll(){
      $all = array();
      $query = "select * from patients";

      $stmt = $this->db_handle->prepare($query);
      $stmt->execute();
      $all = $stmt->fetchAll(PDO::FETCH_ASSOC);

      return $all;
    }


    // free resources that this object is using
    public function __destruct(){ }


  }// end of class

?>