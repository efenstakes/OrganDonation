<?php
   
  include_once("User.php");
  include_once("DbManager.php");


    
  /*
    contain data that has to do with a user.. 
    acts as the superclass for user-related classes
  */
  class Staff extends User {
    private $about_me = "", $specialty = NULL, 
            $institution = array( 'id' => NULL );
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




    public function setInstitution($inst){
      $this->institution = $inst;
      return $this;
    }

    public function getInstitution(){
        return $this->institution;
    }

  


    // save a photo instance
    public function save(){
      $save_result = array('saved'=> false, 'id'=> NULL);

      try{
        $this->db_handle->beginTransaction();

        $query = "insert into staff " 
                  ." ( name, password, institution_id, email, staff_type ) "
                  ." values(?, ?, ?, ?, ?)";
 
        $query_data = array(
                        $this->getName(),
                        $this->getPassword(),
                        $this->getInstitution()['id'],
                        $this->getContacts()['email'],
                        $this->getType()
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
      

    // check if a staff exists
    public function findID(){
      $name = NULL;
      $query = "select * from staff where name = ?";

      $stmt = $this->db_handle->prepare($query);
      $stmt->execute(array($this->getName()));

      $details = $stmt->fetchAll(PDO::FETCH_ASSOC);

      if( $stmt->rowCount() > 0 ){
        $name = $details[0]['id'];
      }

      return $name;
    }// public function exists(){ .. }

    // check if a staff exists
    public function nameUsed(){
      $exists = false;
      $query = "select * from staff where name = ?";

      $stmt = $this->db_handle->prepare($query);
      $stmt->execute(array($this->getName()));

      if( $stmt->rowCount() > 0 ){
         $exists = true;
      }

      return $exists;
    }// public function exists(){ .. }

    // check if a staff exists
    public function accountExists(){
      $exists = false;
      $query = "select * from staff where password = ? AND name = ?";

      $stmt = $this->db_handle->prepare($query);
      $stmt->execute(array( $this->getPassword(), $this->getName() ));
       
      # var_dump($stmt->fetch(PDO::FETCH_ASSOC));

      if( $stmt->rowCount() > 0 ){
         $exists = true;
      }

      return $exists;
    }// public function exists(){ .. }

    // check if a staff exists
    public function exists(){
      $exists = false;
      $query = "select * from staff where id = ?";

      $stmt = $this->db_handle->prepare($query);
      $stmt->execute(array( $this->getID() ));

      if( $stmt->rowCount() > 0 ){
         $exists = true;
      }

      return $exists;
    }// public function exists(){ .. }

    // delete a staff given their id 
    public function delete(){
       $query = "delete from staff where id = ?";
       $stmt = $this->db_handle->prepare($query);
       $stmt->execute(array( $this->getID() ));
    }// end of public function delete(){ .. }




    public function setProperties(){
      $staff = new Staff();
      try{

        //get primary photo details like name etc
        $primary_query = "select * from staff where id = ?";
        $primary_stmt = $this->db_handle->prepare($primary_query);
        $primary_stmt->execute(array( $this->getID() ));
        $primary_results = $primary_stmt->fetch(PDO::FETCH_ASSOC);

        //get primary photo details like name etc
        /*$titles_query = "select specialty from specialty where STAFFID = ?";
        $titles_stmt = $this->db_handle->prepare($titles_query);
        $titles_stmt->execute(array($this->getID()));
        $titles_results = $titles_stmt->fetch(PDO::FETCH_ASSOC);*/

        // combined properties
        $combined_properties = array(
                           'staff'=> $primary_results 
                        );

        $staff = Staff::make($combined_properties);

      }catch(PDOException $e){
        echo($e->getMessage());
      }

      return $staff;
    }

    // make the details of a photo
    // database data is passed in an array then this method returns a photo instance
    public static function make($staff_properties){
      $this_staff = new Staff();

      // unpack the properties from the photo_properties array
      $staff = $staff_properties['staff'];

      $this_staff->setID($staff['id'])
                 ->setPassword($staff['password'])
                 ->setName($staff['name'])
                 ->setInstitution(array( 
                        'id' => $staff['institution_id'] 
                  ))
                 ->setContacts(array(
                           'email' => $staff['email']
                  ))
                 ->setType($staff['staff_type']);

      return $this_staff;
    } // end of public static function make($photo_properties){ .. }

    // return an array with the photo details
    public function getProperties(){
      $properties = array();
      
      $properties['id'] = $this->getID();
      $properties['name'] = $this->getName();
      $properties['password'] = $this->getPassword();
      $properties['contacts'] = $this->getContacts();
      $properties['gender'] = $this->getGender();
      $properties['institution'] = $this->getInstitution();

      return $properties;
    }// end of public function getProperties(){ .. }

    
    public function getAll(){
      $all = array();
      $query = "select * from staff";

      try{
         $stmt = $this->db_handle->prepare($query);
         $stmt->execute();

         $stmt_result = $stmt->fetchAll(PDO::FETCH_ASSOC);

         $all = array_map(function($db_staff){
                   return Staff::make(array( 
                                  'staff'=> $db_staff 
                            ))->getProperties();
         }, $stmt_result);

      }catch(PDOException $e){
        echo($e->getMessage());
      }

      return $all;
    }// end of getAll



    // free resources that this object is using
    public function __destruct(){ }


  }// end of class

?>