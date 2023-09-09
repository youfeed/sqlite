<?php
declare(strict_types=1);
namespace Youloge\Sqlite;
use Webman\Exception; // NotFoundException

class Config{
  protected static $_instance = null;
  public static $CONFIG;
  public static $SQLITE;
  public function __construct(){
    if(self::$_instance === null){
      var_export("Config...init");
      @['dump'=>$dump,'absolute'=>$absolute] = $config = config('plugin.youloge.sqlite.app');
      $sqlite = json_decode(file_get_contents("$absolute/$dump"),true);
      self::$CONFIG = $config;
      self::$SQLITE = $sqlite;
      $this->directory();
      self::$_instance = $this;
    }
    return self::$_instance;
  }
  private static function getInstance(){
    return new self();
  }
  private function directory(){
    $sqlite = self::$SQLITE;
    @['absolute'=>$absolute] = self::$CONFIG;
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
  }
  static public function initialize($dir,$file){
    @['enable'=>$enable,'absolute'=>$absolute,'format'=>$format] = self::getConfig();
    $trim = trim($dir,'./');$explode = explode('/',$trim);$sqlite = self::getSqlite();
    $shift = array_shift($explode);
    while ($shift){
      @[$shift=>$sqlite] = $sqlite;
      $shift = array_shift($explode);
    }
    // 处理 :内存数据库:
    return ['database'=>"$absolute/$trim/$file.$format",'fields'=>$sqlite];
  }
  static public function getConfig(){
    return self::getInstance()::$CONFIG;
  }
  static public function getSqlite(){
    return self::getInstance()::$SQLITE;
  }
}
