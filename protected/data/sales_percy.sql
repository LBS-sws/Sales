DROP TABLE IF EXISTS sal_fivestep;
CREATE TABLE sal_fivestep (
  id int unsigned NOT NULL auto_increment primary key,
  username varchar(30) NOT NULL,
  rec_dt datetime NOT NULL,
  step varchar(10) NOT NULL,
  filename varchar(255) NOT NULL,
  filetype varchar(50),
  status char(1) NOT NULL DEFAULT 'Y',
  city char(5) NOT NULL,
  lcu varchar(30),
  luu varchar(30),
  lcd timestamp default CURRENT_TIMESTAMP,
  lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS sal_fivestep_info;
CREATE TABLE sal_fivestep_info (
  five_id int unsigned NOT NULL,
  field_id varchar(30) not null,
  field_value varchar(2000),
  lcu varchar(30),
  luu varchar(30),
  lcd timestamp default CURRENT_TIMESTAMP,
  lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  UNIQUE KEY fivestepinfo (five_id, field_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS sal_visit_type;
CREATE TABLE sal_visit_type (
  id int unsigned NOT NULL auto_increment primary key,
  name varchar(255) NOT NULL,
  rpt_type varchar(10),
  lcu varchar(30),
  luu varchar(30),
  lcd timestamp default CURRENT_TIMESTAMP,
  lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS sal_visit_obj;
CREATE TABLE sal_visit_obj (
  id int unsigned NOT NULL auto_increment primary key,
  name varchar(255) NOT NULL,
  rpt_type varchar(10),
  lcu varchar(30),
  luu varchar(30),
  lcd timestamp default CURRENT_TIMESTAMP,
  lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS sal_cust_type;
CREATE TABLE sal_cust_type (
  id int unsigned NOT NULL auto_increment primary key,
  name varchar(255) NOT NULL,
  type_group smallint unsigned NOT NULL default 1,
  rpt_type varchar(10),
  city char(5) NOT NULL,
  lcu varchar(30),
  luu varchar(30),
  lcd timestamp default CURRENT_TIMESTAMP,
  lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS sal_cust_district;
CREATE TABLE sal_cust_district (
  id int unsigned NOT NULL auto_increment primary key,
  name varchar(255) NOT NULL,
  city char(5) NOT NULL,
  lcu varchar(30),
  luu varchar(30),
  lcd timestamp default CURRENT_TIMESTAMP,
  lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS sal_custcache;
CREATE TABLE sal_custcache (
  username varchar(30) NOT NULL,
  cust_name varchar(255) NOT NULL,
  cust_alt_name varchar(255),
  cust_person varchar(255),
  cust_person_role varchar(255),
  cust_tel varchar(50),
  district int unsigned,
  street varchar(255),
  cust_type int unsigned,
  lcd timestamp default CURRENT_TIMESTAMP,
  lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  UNIQUE KEY custcache (username, cust_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS sal_custstar;
CREATE TABLE sal_custstar (
  username varchar(30) NOT NULL,
  cust_name varchar(255) NOT NULL,
  cust_vip char(1) NOT NULL DEFAULT 'N',
  lcd timestamp default CURRENT_TIMESTAMP,
  lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  UNIQUE KEY custstar (username, cust_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS sal_visit;
CREATE TABLE sal_visit (
  id int unsigned NOT NULL auto_increment primary key,
  username varchar(30) NOT NULL,
  visit_dt datetime NOT NULL,
  visit_type int unsigned NOT NULL,
  visit_obj varchar(100) NOT NULL,
  cust_type int unsigned NOT NULL,
  cust_name varchar(255) NOT NULL,
  cust_alt_name varchar(255),
  cust_person varchar(255),
  cust_person_role varchar(255),
  cust_tel varchar(50),
  district int unsigned NOT NULL,
  street varchar(255),
  remarks varchar(5000),
  status char(1) NOT NULL DEFAULT 'N',
  status_dt datetime,
  city char(5) NOT NULL,
  lcu varchar(30),
  luu varchar(30),
  lcd timestamp default CURRENT_TIMESTAMP,
  lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS sal_visit_info;
CREATE TABLE sal_visit_info (
  visit_id int unsigned NOT NULL,
  field_id varchar(30) not null,
  field_value varchar(2000),
  lcu varchar(30),
  luu varchar(30),
  lcd timestamp default CURRENT_TIMESTAMP,
  lud timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  UNIQUE KEY visitinfo (visit_id, field_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS sal_queue;
CREATE TABLE sal_queue (
	id int unsigned NOT NULL auto_increment primary key,
	rpt_desc varchar(250) NOT NULL,
	req_dt datetime,
	fin_dt datetime,
	username varchar(30) NOT NULL,
	status char(1) NOT NULL,
	rpt_type varchar(10) NOT NULL,
	ts timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
	rpt_content longblob
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS sal_queue_param;
CREATE TABLE sal_queue_param (
	id int unsigned NOT NULL auto_increment primary key,
	queue_id int unsigned NOT NULL,
	param_field varchar(50) NOT NULL,
	param_value text,
	ts timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS sal_queue_user;
CREATE TABLE sal_queue_user (
	id int unsigned NOT NULL auto_increment primary key,
	queue_id int unsigned NOT NULL,
	username varchar(30) NOT NULL,
	ts timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DELIMITER //
DROP FUNCTION IF EXISTS VisitObjDesc //
CREATE FUNCTION VisitObjDesc(p_obj varchar(100)) RETURNS varchar(5000)
BEGIN
DECLARE done int default false;
DECLARE obj_desc varchar(5000);
DECLARE obj_name varchar(255);
DECLARE cur1 CURSOR FOR
SELECT name
FROM sal_visit_obj
WHERE locate(concat(',"',id,'",'),p_obj) > 0
OR locate(concat('["',id,'"]'), p_obj) > 0 
OR locate(concat(',"',id,'"]'), p_obj) > 0 
OR locate(concat('["',id,'",'), p_obj) > 0;
DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = true;
	
SET obj_desc = '';
	
OPEN cur1;
read_loop: LOOP
FETCH cur1 INTO obj_name;
IF done THEN
LEAVE read_loop;
END IF;
SET obj_desc = IF(obj_desc='', obj_name, concat(obj_desc, ', ', obj_name));
END LOOP;
CLOSE cur1;
RETURN obj_desc;
END //
DELIMITER ;
