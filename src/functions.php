<?php 
use Youloge\Sqlite\Sqlite;     
if(!function_exists('sqlite')){                  
  function sqlite($dir,$file){                     
    return new Sqlite($dir,$file);               
  }    
}  