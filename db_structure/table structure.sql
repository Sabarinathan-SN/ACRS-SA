create database convocation;
use convocation;
drop table students;
CREATE USER 'sn'@'localhost' IDENTIFIED BY '1234';
GRANT ALL PRIVILEGES ON convocation.* TO 'sn'@'localhost';

-- Create the Student table
CREATE TABLE Student (
    Reg_no INT PRIMARY KEY,
    Name VARCHAR(100) NOT NULL,
    Dpt_id INT,
    Degree_id INT,
    Mobile_num VARCHAR(15),
    Mailid VARCHAR(100),
    Address VARCHAR(255),
    Dob DATE,
    Adm_yr YEAR
);
drop table student;
CREATE TABLE Student (
    Reg_no VARCHAR(10) PRIMARY KEY,
    Name VARCHAR(100) NOT NULL,
    Dpt_id INT,
    Degree_id INT,
    Mobile_num VARCHAR(15),
    Mailid VARCHAR(100),
    Address VARCHAR(255),
    Dob DATE,
    Adm_yr YEAR
);

INSERT INTO Student (Reg_no, Name, Dpt_id, Degree_id, Mobile_num, Mailid, Address, Dob, Adm_yr) 
VALUES 
('20Ei1001', 'Abhinav', 1, 1, '1234567890', 'itbase@example.com', '123 Main St, City, Country', '2000-01-01', 2020),
('21IT1001', 'Yogeshwaran', 2, 1, '9876543210', 'yog@example.com', '456 Oak St, City, Country', '1999-05-15', 2020),
('21IT1002', 'Sabarinathan', 1, 1, '5556667777', 'sn@example.com', '789 Elm St, City, Country', '2001-10-20', 2020);
update  student set Reg_no='21Ei1001' where Name='Abhinav';
select * from student;
select * from student;
truncate student;
-- Create the Department table
CREATE TABLE Department (
    Dpt_id INT PRIMARY KEY,
    Dpt_name VARCHAR(100) NOT NULL
);
insert into department values (1,'Information Technology');
truncate  registration;
-- Create the Degree table
CREATE TABLE Degree (
    Deg_id INT PRIMARY KEY,
    Deg_name VARCHAR(100) NOT NULL
);
insert into degree values(2,'M.Tech');

-- Create the Examination Details table
CREATE TABLE Examination_Details (
    Reg_no INT PRIMARY KEY,
    Status_of_passing VARCHAR(20)
);
select * from examination_details;
alter table examination_details add column Certificate_no int;
alter table examination_details modify column reg_no varchar(20);
insert into examination_details values('20Ei1001','Pass',12341);
-- Create the Registration table
drop  table registration;
CREATE TABLE Registration (
    Reg_no VARCHAR(20) PRIMARY KEY,
    Passout_year INT,
    Payment_status VARCHAR(20),
    Payment_date DATE,
    Mode_of_collection VARCHAR(20)
);
select * from registration;
truncate table registration;
truncate table registration;
CREATE TABLE InPersonCollection (
    Reg_no VARCHAR(20),
    Accompanying_persons INT,
    Food_preference VARCHAR(20),
    Food_cost INT,
    PRIMARY KEY (Reg_no)
);
drop table inpersoncollection;
truncate table inpersoncollection;
select * from registration;


alter table inpersoncollection drop column food_cost;
select * from inpersoncollection;
CREATE TABLE ByPost (
    Reg_no VARCHAR(20),
    Address_for_sending VARCHAR(255),
    PRIMARY KEY (Reg_no)
);
select * from bypost;
select * from inpersoncollection;
desc inpersoncollection;
drop table registration;
select * from registration;
-- Create the Seats table
CREATE TABLE Seats (
    Seat_id INT PRIMARY KEY,
    Location VARCHAR(100) NOT NULL
);

-- Create the Ticket table
CREATE TABLE Ticket (
    Seat_id INT,
    Reg_no INT,
    Time TIME,
    Token_number VARCHAR(20),
    PRIMARY KEY (Seat_id, Reg_no)
);
desc registration;
select * from registration;
ALTER TABLE Registration ADD COLUMN amount int;

-- Create temporary reg table
CREATE TABLE T_reg(
	Reg_no VARCHAR(10) primary key,
    Email VARCHAR(25),
    Password VARCHAR(10),
        one VARCHAR(30),
    two VARCHAR(30),
    prev_password VARCHAR(10));
ALTER TABLE T_reg MODIFY email VARCHAR(255);


truncate table T_reg;
select * from T_reg;
delete from t_reg where reg_no='20Ei1001'; 
update t_reg set email='sabarisankar2603@gmail.com' where Reg_no='21IT1002';
alter table T_reg drop column prev_password;
select one,two from t_reg where email='sabarisankar2603@gmail.com';

CREATE TABLE Documents (
    Reg_no VARCHAR(50) PRIMARY KEY,
    Aadhar_card VARCHAR(255) NOT NULL,
    TC VARCHAR(255) NOT NULL
);
truncate table documents;
select * from documents;

SHOW DATABASES LIKE 'convocation';




-- Create the Admin table
CREATE TABLE Admin (
    Admin_id INT AUTO_INCREMENT PRIMARY KEY,
    Username VARCHAR(50) NOT NULL,
    Email VARCHAR(100) NOT NULL,
    Password VARCHAR(255) NOT NULL,
    Role_id INT,
    FOREIGN KEY (Role_id) REFERENCES Roles(Role_id)
);

drop table Admin;

CREATE TABLE Roles (
    Role_id INT AUTO_INCREMENT PRIMARY KEY,
    Role_name VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE Permissions (
    Permission_id INT AUTO_INCREMENT PRIMARY KEY,
    Permission_name VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE Role_Permissions (
    Role_id INT,
    Permission_id INT,
    PRIMARY KEY (Role_id, Permission_id),
    FOREIGN KEY (Role_id) REFERENCES Roles(Role_id),
    FOREIGN KEY (Permission_id) REFERENCES Permissions(Permission_id)
);

ALTER TABLE Admin ADD COLUMN Role_id INT,
ADD FOREIGN KEY (Role_id) REFERENCES Roles(Role_id);

INSERT INTO Roles (Role_name) VALUES ('Super Admin'), ('Admin'), ('Moderator');
INSERT INTO Permissions (Permission_name) VALUES ('View Users'), ('Edit Users'), ('Delete Users'), ('View Payments'), ('Edit Payments');

INSERT INTO Role_Permissions (Role_id, Permission_id) VALUES 
(1, 1), (1, 2), (1, 3), (1, 4), (1, 5), -- Super Admin has all permissions
(2, 1), (2, 2), (2, 4), (2, 5), -- Admin has view/edit users and payments
(3, 1), (3, 4); -- Moderator has view users and payments
select * from admin;
desc admin;
select * from roles;

INSERT INTO Roles (Role_name) VALUES ('Super Admin'), ('Admin'), ('Moderator');
