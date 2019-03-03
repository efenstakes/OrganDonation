
-- 
drop database if exists daton;

create database if not exists daton;


use daton;


create table patients (
    id int auto_increment,
    name varchar(34) not null,
    password varchar(200) not null,
    city varchar(20),
    email varchar(30),
    dob datetime default NOW(),
    gender ENUM('MALE', 'FEMALE', 'OTHER'),
    user_type ENUM('DONOR', 'PATIENT', 'BOTH') default 'BOTH',
    blood_type ENUM('O','A','B', 'O-','O+', 'AB','B+','A+','B-', 'AB-', 'AB+'),
    primary key(id)
);




create table institutions (
    id int auto_increment,
    name varchar(34) not null,
    city varchar(20),
    lat varchar(20),
    lng varchar(20),
    email varchar(30),
    primary key(id)
);

create table staff(
    id int auto_increment,
    name varchar(34) not null,
    password varchar(200)not null,
    institution_id int,
    email varchar(30),
    staff_type enum('ADMIN','REGULAR') default 'REGULAR',
    foreign key(institution_id) references institutions(id),
    primary key(id)
);

create table requests (
    id int auto_increment,
    request_for enum('BLOOD', 'ORGAN'),
    institution_from int,
    institution_to int,
    patient_id int,
    request_from ENUM('PATIENT','HOSPITAL'),
    broadcast enum('TRUE','FALSE'),
    request_text longtext,
    date_of_request datetime, 
    foreign key(institution_to) references institutions(id),
    foreign key(institution_from) references institutions(id),
    primary key(id)
);


