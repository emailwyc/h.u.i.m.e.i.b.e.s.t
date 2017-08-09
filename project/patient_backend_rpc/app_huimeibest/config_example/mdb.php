<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// Generally will be localhost if you're querying from the machine that Mongo is installed on
$config['mdb_host'] = "10.172.241.100";

// Generally will be 27017 unless you've configured Mongo otherwise
$config['mdb_port'] = 27017;

// The database you want to work from (required)
$config['mdb_db'] = "backend_staging";

// Leave blank if Mongo is not running in auth mode
$config['mdb_user'] = "";
$config['mdb_pass'] = "";

// Persistant connections
$config['mdb_persist'] = TRUE;
$config['mdb_persist_key'] = 'ci_mongo_persist';

// Get results as an object instead of an array
$config['mdb_return'] = 'array'; // Set to object

// When you run an insert/update/delete how sure do you want to be that the database has received the query?
// safe = the database has receieved and executed the query
// fysnc = as above + the change has been committed to harddisk <- NOTE: will introduce a performance penalty
$config['mdb_query_safety'] = 'w';

// Supress connection error password display
$config['mdb_supress_connect_error'] = TRUE;

// If you are having problems connecting try changing this to TRUE
$config['host_db_flag'] = True;
