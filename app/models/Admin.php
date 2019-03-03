<?php
   
  include_once("User.php");
  include_once("DbManager.php");


  class Admin extends User {
    private $db_handle = NULL;

    // set the default values for the object variables
    public function __construct($name = "", $id = "", $password = ""){
        $db_manager = new DbManager();
        $this->db_handle = $db_manager->getHandle();
    }  

    // 
    public function __toString(){
       return "id :". $this->getID() ." , name :". $this->getName();
    } 




    // save a photo instance
    public function save(){
      $save_result = array('saved'=> false, 'id'=> NULL);

      try{
        $this->db_handle->beginTransaction();

        $query = "insert into admins " 
                  ." ( name, password, admin_type ) values(?, ?, ?)";
 
        $query_data = array(
                        $this->getName(),
                        $this->getPassword(),
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
      

    // check if a admin exists
    public function findID(){
      $name = NULL;
      $query = "select * from admins where name = ?";

      $stmt = $this->db_handle->prepare($query);
      $stmt->execute(array($this->getName()));

      $details = $stmt->fetchAll(PDO::FETCH_ASSOC);

      if( $stmt->rowCount() > 0 ){
        $name = $details[0]['id'];
      }

      return $name;
    }// public function exists(){ .. }

    // check if an admin name is used
    public function nameUsed(){
      $exists = false;
      $query = "select * from admins where name = ?";

      $stmt = $this->db_handle->prepare($query);
      $stmt->execute(array( $this->getName() ));

      if( $stmt->rowCount() > 0 ){
         $exists = true;
      }

      return $exists;
    }// public function exists(){ .. }

    // check if a admin exists
    public function accountExists(){
      $exists = false;
      $query = "select * from admins where password = ? AND name = ?";

      $stmt = $this->db_handle->prepare($query);
      $stmt->execute(array( $this->getPassword(), $this->getName() ));
       
      # var_dump($stmt->fetch(PDO::FETCH_ASSOC));

      if( $stmt->rowCount() > 0 ){
         $exists = true;
      }

      return $exists;
    }// public function exists(){ .. }

    // check if an admin exists
    public function exists(){
      $exists = false;
      $query = "select * from admins where id = ?";

      $stmt = $this->db_handle->prepare($query);
      $stmt->execute(array( $this->getID() ));

      if( $stmt->rowCount() > 0 ){
         $exists = true;
      }

      return $exists;
    }// public function exists(){ .. }

    // delete an admin given their id 
    public function delete(){
       $query = "delete from admin where id = ?";
       $stmt = $this->db_handle->prepare($query);
       $stmt->execute(array( $this->getID() ));
    }// end of public function delete(){ .. }




    public function setProperties(){
      $admin = new Admin();
      try{

        //get primary admin details like name etc
        $primary_query = "select * from admins where id = ?";
        $primary_stmt = $this->db_handle->prepare($primary_query);
        $primary_stmt->execute(array( $this->getID() ));
        $primary_results = $primary_stmt->fetch(PDO::FETCH_ASSOC);

        // combined properties
        $combined_properties = array(
                           'admin'=> $primary_results 
                        );

        $admin = Admin::make($combined_properties);

      }catch(PDOException $e){
        echo($e->getMessage());
      }

      return $admin;
    }

    // make the details of a photo
    // database data is passed in an array then this method returns a photo instance
    public static function make($admin_properties){
      $this_admin = new Admin();

      // unpack the properties from the admin_properties array
      $admin = $staff_properties['admin'];

      $this_admin->setID($admin['id'])
                 ->setPassword($admin['password'])
                 ->setName($admin['name'])
                 ->setType($admin['admin_type']);

      return $this_admin;
    } // end of public static function make($photo_properties){ .. }

    // return an array with the admin details
    public function getProperties(){
      $properties = array();
      
      $properties['id'] = $this->getID();
      $properties['name'] = $this->getName();
      $properties['password'] = $this->getPassword();
      $properties['type'] = $this->getType();

      return $properties;
    }// end of public function getProperties(){ .. }

    
    public function getAll(){
      $all = array();
      $query = "select * from admins";

      try{
         $stmt = $this->db_handle->prepare($query);
         $stmt->execute();

         $stmt_result = $stmt->fetchAll(PDO::FETCH_ASSOC);

         $all = array_map(function($db_staff){
                   return Admin::make(array( 'admin'=> $db_staff ))->getProperties();
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