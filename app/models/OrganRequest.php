<?php
   
  include_once("DbManager.php");


  class OrganRequest {
    private $id = NULL, $broadcast = true, $sorted = false, $request_for = NULL, $request_text = "";
    private $institution = array( 
                             'from'=> array( 'id'=> NULL ),
                             'to'=> array( 'id'=> NULL )
     );
    private $patient = array( 'id'=> NULL );

    private $db_handle = NULL;

    // set the default values for the object variables
    public function __construct(){
        $db_manager = new DbManager();
        $this->db_handle = $db_manager->getHandle();
    }  

    // 
    public function __toString(){
       return "id :". $this->getID() ." , name :". $this->getName();
    } 



    public function setID($id){
      $this->id = $id;
      return $this;
    } 
    public function setBroadcast($bd){
       $this->broadcast = $db;       
       return $this;
    } 
    public function setSorted($srt){
      $this->sorted = $srt;
      return $this;
    } 
    public function setRequestFor($request_for){
      $this->request_for = $request_for;
      return $this;
    } 
    public function setRequestText($request_text){
      $this->request_text = $request_text;
      return $this;
    } 
    public function setInstitution($institution){
      $this->institution = $institution;
      return $this;
    } 
    public function setPatient($patient){
       $this->patient = $patient;      
       return $this;
    } 

    public function getID(){
       return $this->id;
    } 
    public function getBroadcast(){
       return $this->broadcast;
    } 
    public function getSorted(){
       return $this->sorted;
    } 
    public function getRequestFor(){
       return $this->request_for;
    } 
    public function getRequestText(){
       return $this->request_text;
    } 
    public function getInstitution(){
       return $this->institution;
    } 
    public function getPatient(){
       return $this->patient;
    } 

    // save an organ request instance
    public function save(){
      $save_result = array('saved'=> false, 'id'=> NULL);

      try{
        $this->db_handle->beginTransaction();

        $query = "insert into organ_requests " 
                  ." ( request_for, request_text, institution_from, institution_to, patient_id ) "
                  ." values(?, ?, ?, ?, ?)";
 
        $query_data = array(
                        $this->getRequestFor(),
                        $this->getRequestText(),
                        $this->getInstitution()['from']['id'],
                        $this->getInstitution()['to']['id'],
                        $this->getPatient()['id']
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
      
    // check if a request exists
    public function exists(){
      $exists = false;
      $query = "select * from organ_requests where id = ?";

      $stmt = $this->db_handle->prepare($query);
      $stmt->execute(array( $this->getID() ));

      if( $stmt->rowCount() > 0 ){
         $exists = true;
      }

      return $exists;
    }// public function exists(){ .. }

    // delete a request given their id 
    public function delete(){
       $query = "delete from organ_requests where id = ?";
       $stmt = $this->db_handle->prepare($query);
       $stmt->execute(array( $this->getID() ));
    }// end of public function delete(){ .. }




    public function setProperties(){
      $request = new OrganRequest();
      try{

        //get primary request details
        $primary_query = "select * from organ_requests where id = ?";
        $primary_stmt = $this->db_handle->prepare($primary_query);
        $primary_stmt->execute(array( $this->getID() ));
        $primary_results = $primary_stmt->fetch(PDO::FETCH_ASSOC);

        // combined properties
        $combined_properties = array(
                           'request'=> $primary_results 
                        );

        $request = OrganRequest::make($combined_properties);

      }catch(PDOException $e){
        echo($e->getMessage());
      }

      return $request;
    } // public function setProperties(){ .. }


    // make the details of a request
    // database data is passed in an array then this method returns a photo instance
    public static function make($request_properties){
      $this_request = new OrganRequest();

      // unpack the properties from the admin_properties array
      $request = $staff_properties['request'];

      $this_request->setID($request['id'])
                 ->setBroadcast($request['broadcast'])
                 ->setSorted($request['sorted'])
                 ->setRequestFor($request['request_for'])
                 ->setRequestText($request['request_text'])
                 ->setInstitution(array(
                      'from'=> array( 'id'=> $request['institution_from'] ),
                      'to'=> array( 'id'=> $request['institution_to'] )
                 ))
                 ->setPatient(array( 'id'=> $request['patient_id'] ));

      return $this_request;
    } // end of public static function make($photo_properties){ .. }

    // return an array with the request details
    public function getProperties(){
      $properties = array();
      
      $properties['id'] = $this->getID();
      $properties['broadcast'] = $this->getBroadcast();
      $properties['sorted'] = $this->getSorted();
      $properties['request_for'] = $this->getRequestFor();
      $properties['request_text'] = $this->getRequestText();
      $properties['institution'] = $this->getInstitution();
      $properties['patient'] = $this->getPatient();

      return $properties;
    }// end of public function getProperties(){ .. }

    
    public function getAll(){
      $all = array();
      $query = "select * from organ_requests";

      try{
         $stmt = $this->db_handle->prepare($query);
         $stmt->execute();

         $stmt_result = $stmt->fetchAll(PDO::FETCH_ASSOC);

         $all = array_map(function($db_request){
                   return OrganRequest::make(array( 'request'=> $db_request ))->getProperties();
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