<?php
declare(strict_types=1);
namespace Youloge\Sqlite;

use Webman\Exception; // NotFoundException
// use Webman\Bootstrap;

class Sqlite
{
  // 单例对象
  protected static $_instance = null;
  // 数据库连接池
  // 数据库配置文件
  protected static $uuid;
  protected static $POOL;
  private static $CONFIG;
  private static $SQLITE;
  // 初始化DB文件
  public function __construct($dir,$file,$table){
    if(!isset(self::$CONFIG)){
      var_export('self::$CONFIG');
      $config = config('plugin.youloge.sqlite.app');
      $sqlite = config('plugin.youloge.sqlite.sqlite');
      self::$CONFIG = $config;
      self::$SQLITE = $sqlite;
    }
    @['absolute'=>$absolute,'format'=>$format] = self::$CONFIG;
    // 检查目录
    $trim = trim($dir,'./:');
    $replace = str_replace("/", ".", $trim);
    @[$table=>$field] = config($replace ? "plugin.youloge.sqlite.sqlite.$replace" : "plugin.youloge.sqlite.sqlite");
    if($field==null) throw new \Exception('目录错误或未定义数据表');
    $db = new \SQLite3("$absolute/$trim/$file.$format");
    ($table && $field) && $db->exec("create table if not exists $table($field);");
    // print_r($field);
    // 分配UUID + 初始参数 
    $uuid = session_create_id();
    self::$uuid = $uuid;
    self::$POOL[$uuid] = [
      'DB'=>$db,
      'absolute'=>"$absolute/$trim/$file.$format",
      'table'=>$table,
      'select'=>'*',
      'order'=>'',
      'set'=>[],
      'where'=>'',
      'limit'=>'LIMIT 10',
      'offset'=>'OFFSET 0',
    ];
    // 是否首次
    if(self::$_instance){ var_export(["单例:$uuid"]); return self::$_instance; }
    // 映射 + 创建 文件目录
    $list = [];
    $iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($sqlite), \RecursiveIteratorIterator::SELF_FIRST);
    foreach ($iterator as $key => $value) {
      if (is_array($value)) {
        $path = '';
        foreach (range(0, $iterator->getDepth()) as $depth) {
          $path .= $iterator->getSubIterator($depth)->key() . '/';
        }
        $list[] = rtrim($path, '/');
      }
    }
    foreach ($list as $relative){
      is_dir("$absolute/$relative") || mkdir("$absolute/$relative",0777,true);
    }
    var_export($list);
    self::$_instance = $this;
  }
  // 销毁实例
  public function __destruct(){
    self::$POOL[self::$uuid]['DB']->close();
    unset(self::$POOL[self::$uuid]);
  }
  // PDO 句柄
  public function PDO(){
    @['DB'=>$db] = self::$POOL[self::$uuid];
    return $db;
  }
  // version
  public function version()
  {
    @['DB'=>$db] = self::$POOL[self::$uuid];
    return $db->version(); 
  }
  // select 
  public function select($params='*'){
    self::$POOL[self::$uuid]['select'] = implode(',',$params);
    return $this;
  }
  // where 
  public function where($params,$default=[]){
    foreach($params as $key=>$val){
      $default[] = sprintf("`%s` = %s",$key,is_string($val) ? "'$val'" : $val);
    } 
    self::$POOL[self::$uuid]['where'] = sprintf("WHERE %s",implode(' AND ',$default));
    return $this;
  }
  // set
  public function set($data){
    if(array_is_list($data)){
      $vals = [];
      foreach($data as $item){
        @[$keys,$val] = $this->ToData($item);
        $vals[] = $val;
      }
      $vals = implode(",",$vals);
    }else{
      @[$keys,$vals] = $this->ToData($data);
      $sets = $this->ToWhere($data);
    }
    self::$POOL[self::$uuid]['set'] = [$keys,$vals,$sets??''];
    return $this;
  }
  // limit
  public function limit($int){
    self::$POOL[self::$uuid]['limit'] = "LIMIT $int";
    return $this;
  }
  // offset
  public function offset($int){
    self::$POOL[self::$uuid]['offset'] = "OFFSET $int";
    return $this;
  }
  // row_array
  public function row_array($flag=false){
    @['DB'=>$db,'table'=>$table,'where'=>$where,'offset'=>$offset,'select'=>$select,'order'=>$order] = self::$POOL[self::$uuid];
    $string = sprintf("SELECT %s FROM %s %s %s %s %s",$select,$table,$where,$order ? "ORDER BY $order" : "","LIMIT 1",$offset);
    return $flag ? $string : $db->querySingle($string,true);
  }
  // result_array
  public function result_array($flag=false){
    @['DB'=>$db,'table'=>$table,'where'=>$where,'limit'=>$limit,'offset'=>$offset,'select'=>$select,'order'=>$order] = self::$POOL[self::$uuid];
    $string = sprintf("SELECT %s FROM %s %s %s %s %s",$select,$table,$where,$order ? "ORDER BY $order" : "",$limit,$offset);
    if($flag) return $string;
    $query = $db->query($string);
    while($row = $query->fetchArray(SQLITE3_ASSOC)){
      $result[] = $row;
    }
    return $result ?? [];
  }
  // count_array
  public function count_array($flag=false){
    @['DB'=>$db,'table'=>$table,'where'=>$where,'limit'=>$limit,'offset'=>$offset,'select'=>$select,'order'=>$order] = self::$POOL[self::$uuid];
    $string = sprintf("SELECT %s FROM %s %s %s %s %s",$select,$table,$where,$order ? "ORDER BY $order" : "",$limit,$offset);
    if($flag) return $string;
    $query = $db->query($string);
    while($row = $query->fetchArray(SQLITE3_ASSOC)){
      $list[] = $row;
    }
    @['count'=>$count] = $db->querySingle(sprintf("SELECT COUNT(*) AS count FROM %s %s",$table,$where),true);
    return ['list'=>$list??[],'count'=>$count??0];
  }
  // insert
  public function insert($flag=false){
    $uuid = self::$uuid;@['DB'=>$db,'table'=>$table,'set'=>[$keys,$vals]] = self::$POOL[$uuid];
    $string = sprintf("INSERT INTO %s %s VALUES %s ",$table,$keys,$vals);
    return $flag ? $string : (@$db->exec($string) ? $db->lastInsertRowID() : false);
  }
  // update
  public function update($flag=false){
    $uuid = self::$uuid;@['DB'=>$db,'table'=>$table,'where'=>$where,'set'=>[$keys,$vals,$sets]] = self::$POOL[$uuid];
    $string = sprintf("UPDATE %s SET %s %s",$table,$sets,$where);
    return $flag ? $string : (@$db->exec($string) ? $db->changes() : false);
  }
  // replace
  // delete
  public function delete($flag=false){
    @['DB'=>$db,'table'=>$table,'where'=>$where] = self::$POOL[self::$uuid];
    $string = sprintf("DELETE FROM %s %s",$table,$where);
    return $flag ? $string : (@$db->exec($string) ? $db->changes() : false);
  }
  // ToData
  private function ToData($array){
    $keys = trim(json_encode(array_keys($array),320),'[]');
    $vals = trim(json_encode(array_values($array),320),'[]');
    return ["($keys)","($vals)"];
  }
  // ToOrder
  private function ToOrder($array){
    return implode(',',$array);
  }
  // ToWhere
  private function ToWhere($array,$separator = 'AND')
  {
    foreach($array as $k=>$v){
      $list[] = sprintf("`%s` = %s",$k,is_string($v) ? "'$v'" : $v);
    }
    return implode(" $separator ",$list);
  }
}