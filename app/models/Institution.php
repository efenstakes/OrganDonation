<?php
   
  include_once("User.php");
  include_once("DbManager.php");


    
  /*
    
  */
  class Institution{
    private $id = NULL, $name = NULL, $type = NULL;
    private $location = array( 
        'lat' => NULL, 'lng' => NULL, 'city'=> NULL 
    );
    private $staff = array();
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



    /** 
        @@Mutators
    **/

    // update the object's name property
    public function setName($name){
        $this->name = $name;
        return $this;
    }

    // update the object's id property
    public function setID($id){
        $this->id = $id;
        return $this;
    }

    // update the object's type property
    public function setType($type){
        $this->type = $type;
        return $this;
    }

    // update the object's location property
    public function setLocation($loc){
        $this->location = $loc;
        return $this;
    }

    // @@set the contacts property of this object
    public function setContacts($conts){
        $this->contacts = $conts;
        return $this;
    }

    // @@set the staff property of this object
    public function setStaff($staff){
        $this->staff = $staff;
        return $this;
    }


    /** 
      @accessors
    **/

    // get the object's name property
    public function getName(){
      return $this->name;
    }

    // get the object's id property
    public function getID(){
      return $this->id;
    }


    // get the object's type property
    public function getType(){
       return $this->type;
    }

    // get the object's description property
    public function getDescription(){
         return $this->description;
    }

    // get the object's pic property
    // @return type array('city'=> city, 'country'=> country)
    public function getLocation(){
          return $this->location;
    }

    // @return type array('phone'=> phone, 'email'=> email)
    public function getContacts(){
      return $this->contacts;
    }

    // @return type array( )
    public function getStaff(){
      return $this->staff;
    }
  


    // save an institution instance
    public function save(){
      $save_result = array('saved'=> false, 'id'=> NULL);

      try{
        $this->db_handle->beginTransaction();

        $query = "insert into institutions " 
                  ." ( name, city, lat, lng, email ) "
                  ." values( ?, ?, ?, ?, ? )";
 
        $query_data = array(
                        $this->getName(),
                        $this->getLocation()['city'],
                        $this->getLocation()['lat'],
                        $this->getLocation()['lng'],
                        $this->getContacts()['email']
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
      

    // check if a institution exists
    public function findID(){
      $name = NULL;
      $query = "select * from institutions where name = ?";

      $stmt = $this->db_handle->prepare($query);
      $stmt->execute(array($this->getName()));

      $details = $stmt->fetchAll(PDO::FETCH_ASSOC);

      if( $stmt->rowCount() > 0 ){
        $name = $details[0]['ID'];
      }

      return $name;
    }// public function exists(){ .. }

    // check if a institution exists
    public function nameUsed(){
      $exists = false;
      $query = "select * from institutions where name = ?";

      $stmt = $this->db_handle->prepare($query);
      $stmt->execute(array($this->getName()));

      if( $stmt->rowCount() > 0 ){
         $exists = true;
      }

      return $exists;
    }// public function exists(){ .. }


    // check if a institution exists
    public function exists(){
      $exists = false;
      $query = "select * from institutions where id = ?";

      $stmt = $this->db_handle->prepare($query);
      $stmt->execute(array( $this->getID() ));

      if( $stmt->rowCount() > 0 ){
         $exists = true;
      }

      return $exists;
    }// public function exists(){ .. }

    // delete a institution given their id 
    public function delete(){
       $query = "delete from institutions where id = ?";
       $stmt = $this->db_handle->prepare($query);
       $stmt->execute(array( $this->getID() ));
    }// end of public function delete(){ .. }




    public function setProperties(){
      $institution = new Institution();
      try{

        //get primary photo details like name etc
        $primary_query = "select * from institutions where id = ?";
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
                           'institution'=> $primary_results 
                        );

        $institution = Institution::make($combined_properties);

      }catch(PDOException $e){
        echo($e->getMessage());
      }

      return $institution;
    }

    // make the details of a photo
    // database data is passed in an array then this method returns a photo instance
    public static function make($institution_properties){
      $this_institution = new Institution();

      // unpack the properties from the photo_properties array
      $institution = $institution_properties['institution'];

      $this_institution->setID($institution['id'])
                 ->setName($institution['name'])
                 ->setLocation(array( 
                        'lat' => $institution['lat'],
                        'lng' => $institution['lng'],
                        'city' => $institution['city'], 
                  ))
                 ->setContacts(array(
                           'email' => $institution['email']
                  ));

      return $this_institution;
    } // end of public static function make($photo_properties){ .. }

    // return an array with the institution details
    public function getProperties(){
      $properties = array();
      
      $properties['id'] = $this->getID();
      $properties['name'] = $this->getName();
      $properties['contacts'] = $this->getContacts();
      $properties['location'] = $this->getLocation();
      $properties['staff'] = $this->getStaff();

      return $properties;
    }// end of public function getProperties(){ .. }

    
    public function getAll(){
      $all = array();
      $query = "select * from institutions";

      try{
         $stmt = $this->db_handle->prepare($query);
         $stmt->execute();

         $stmt_result = $stmt->fetchAll(PDO::FETCH_ASSOC);

         $all = array_map(function($db_institution){
                   return Institution::make(array( 
                                  'institution'=> $db_institution 
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