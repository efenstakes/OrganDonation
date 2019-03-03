<?php
  
  include_once("SocialEntity.php");
  
  /*
    contain data that has to do with a user.. 
    acts as the superclass for user-related classes
  */
  class User extends SocialEntity {
    private $password = "", $about_me = "", $gender = 'FEMALE', $ref = "";
    private $blood_type = NULL, $dob = NULL;


    // set the default values for the object variables
    public function __construct($name = "", $id = "", $password = ""){
      	super::__construct($name, $id, $password);
   	    $this->type = "user";
   	    $this->location = NULL;
    }  

    // 
    public function __toString(){
       return "id :". $this->getID() ." , name :". $this->getName();
    } 
      
     

    public function setDob($dob){
      $this->dob = $dob;
      return $this;
    }
    public function setBloodType($btype){
      $this->blood_type = $btype;
      return $this;
    }


    public function getDob(){
        return $this->dob;
    }

    public function getBloodType(){
        return $this->blood_type;
    }
 
    /**
        setter methods
    **/

    // update the object's about me property
    public function setAboutMe($abm){
      	$this->about_me = $abm;
      	return $this;
    }

    public function setRef($ref){
        $this->ref = $ref;
        return $this;
    }


    public function setGender($gen){
      $this->gender = $gen;
      return $this;
    }
     

    /** 
        the getter methods
    **/


    // get the object's about me property
    public function getAboutMe(){
      	return $this->about_me;
    }
      
    public function getGender(){
      return $this->gender;
    } 

    public function getRef(){
        return $this->ref;
    }


    // free resources that this object is using
    public function __destruct(){ }


  }// end of class


?>