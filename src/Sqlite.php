<?php
declare(strict_types=1);
namespace Youloge\Sqlite;
use Webman\Exception; 

// #[\ReturnTypeWillChange]
class Sqlite extends \SQLite3{
  function __construct($dir,$file){
    @['database'=>$database,'fields'=>$fields] = Config::initialize($dir,$file);
    $open = self::open($database);
    // 文件初始表
    foreach($fields as $key=>$val){
      is_string($val) && self::exec("create table if not exists $key($val);");
    }
  }
  static public function version():array{
    return [...parent::version(),...Config::getConfig()];
  }
  // insert
  public function insert($table, $data){
    if(array_is_list($data)){
      $vals = [];
      foreach($data as $item){
        @[$keys,$val] = $this->json_trim($item);
        $vals[] = $val;
      }
      $vals = implode(",",$vals);
    }else{
      @[$keys,$vals] = $this->json_trim($data);
    }
    $string = sprintf("INSERT INTO %s %s VALUES %s ",$table,$keys,$vals);
    return @self::exec($string) ? self::lastInsertRowID() : false;
  }
    // update
  public function update($table, $data, $where){
    foreach($data as $key=>$val){
      $default[] = sprintf("`%s` = %s",$key,is_string($val) ? "'$val'" : $val);
    } 
    $set = implode(",",$default);
    foreach($where as $key=>$val){
      $wheres[] = is_string($key) ? sprintf("`%s` = %s",$key,is_string($val) ? "'$val'" : $val) : $val;
    } 
    $whered = implode(' AND ',$wheres);
    $string = sprintf("UPDATE %s SET %s WHERE %s",$table,$set,$whered);
    return  @self::exec($string) ? self::changes() : $string;
  }
  // delete
  public function delete($table,$where){
    foreach($where as $key=>$val){
      $wheres[] = is_string($key) ? sprintf("`%s` = %s",$key,is_string($val) ? "'$val'" : $val) : $val;
    }
    $whered = implode(' AND ',$wheres);
    $string = sprintf("DELETE FROM %s WHERE %s",$table,$whered);
    return @self::exec($string) ? self::changes() : $string;
  }
  // Replace
  // row_array
  public function row_array($table, $columns, $where,$order=false){
    $columns = is_string($columns) ? $columns : implode(',',$columns);
    foreach($where as $key=>$val){
      $wheres[] = is_string($key) ? sprintf("`%s` = %s",$key,is_string($val) ? "'$val'" : $val) : $val;
    }
    $whered = implode(' AND ',$wheres);
    $orderd = $order ? sprintf("ORDER BY %s",is_string($order) ? $order : implode(',',$order)) : '';
    $string = sprintf("SELECT %s FROM %s WHERE %s %s",$columns,$table,$whered,$orderd);
    return @self::querySingle($string,true);
  }
  // result_array
  public function result_array($table, $columns, $where,$limit=10,$offset=0,$order=false){
    $columns = is_string($columns) ? $columns : implode(',',$columns);
    foreach($where as $key=>$val){
      $wheres[] = is_string($key) ? sprintf("`%s` = %s",$key,is_string($val) ? "'$val'" : $val) : $val;
    }
    $whered = implode(' AND ',$wheres);
    $orderd = $order ? sprintf("ORDER BY %s",is_string($order) ? $order : implode(',',$order)) : '';
    $string = sprintf("SELECT %s FROM %s WHERE %s %s LIMIT %s OFFSET %s",$columns,$table,$whered,$orderd,$limit,$offset);
    $query = self::query($string);
    return $this->result($query);
  }
  // count_array
  public function count_array($table, $columns, $where,$limit=10,$offset=0,$order=false){
    $columns = is_string($columns) ? $columns : implode(',',$columns);
    foreach($where as $key=>$val){
      $wheres[] = is_string($key) ? sprintf("`%s` = %s",$key,is_string($val) ? "'$val'" : $val) : $val;
    }
    $whered = implode(' AND ',$wheres);
    $orderd = $order ? sprintf("ORDER BY %s",is_string($order) ? $order : implode(',',$order)) : '';
    $string = sprintf("SELECT %s FROM %s WHERE %s %s LIMIT %s OFFSET %s",$columns,$table,$whered,$orderd,$limit,$offset);
    @['count'=>$count] = @self::querySingle(sprintf("SELECT count(*) as count FROM %s WHERE %s",$table,$whered),true);
    $list = $this->result(self::query($string));
    return ['count'=>$count,'limit'=>$limit,'offset'=>$offset,'list'=>$list];
  }
  // 如果值为 JSON字符串 用json_encode($html,JSON_HEX_APOS)转义
  private function json_trim($array){
    return [
      sprintf("(%s)",trim(json_encode(array_keys($array),320),'[]')),
      sprintf("('%s')",implode("','",array_values($array)))
    ];
  }
  //
  private function result($query){
    while($row = $query->fetchArray(SQLITE3_ASSOC)){
      $result[] = $row;
    }
    return $result ?? [];
  }
}