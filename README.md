# Youloge.Sqlite重构版本 Webman 基础插件 

### 项目介绍 

Sqlite3 插件：它是对标`fopen 函数`的，性能还是不错了，像一般性的个人博客，企业官网，完全都是可以hold住的。
其中`Sqlite 的内存型`反应时间是`ns`级别的，其他数据库都会有网络开销可以做到`ms`级别。代码很少就100多行

> 这二天帮代码升级了下，直接继承`SQLite3`类进行扩展官方类[php.net/sqlite3](https://www.php.net/manual/en/book.sqlite3.php)，我在官方基础上扩展了几个功能，从而实现自动表映射。同时将表配置文件放入到数据库同目录下，迁移时候直接复制即可。

- `Sqlite`的优缺点客观看待，他真的非常适合打日志~

### 项目地址

[Github Youloge.sqlite](https://github.com/youfeed/sqlite) Star我 `我们一起做大做强`

### 安装

```php
composer require youloge/sqlite
```
> 插件已经 引入了以下助手函数 `不需要在引入了`可以直接使用`sqlite()`
``` php  
use Youloge\Sqlite\Sqlite;     
if(!function_exists('sqlite')){                  
  function sqlite($dir,$file){                     
    return new Sqlite($dir,$file);               
  }
}
// 任意地方 使用
$db = sqlite('文件目录/目录','文件名(不包含后缀)'); // 返回是一个`SQLite3 类`
$db::version(); // 返回版本/配置
var_export(get_class_methods($db)); // 打印全部方法

```

### 配置文件

- Sqlite 没什么远程管理工具 配置文件是关键的关键
- 位置：config/plugin/youloge/app.php
- 将`绝对路径`配置到挂载盘之类的可以很好的和`其他日志服务`相结合
```php
<?php
return [
  'enable' => true,
  // 绝对路径 需要 / 开头 结尾 不需要
  'absolute'=>'C:/Users/Micateam/Desktop/youloge/composer/public',
  // 相对路径 数据库配置`JSON`格式 + MD5后续加入 方式配置变动
  'dump'=>'youloge.sqlite.json',
  // 文件后缀 你改成mp4都没问题 Sqlite3 能识别
  'format'=>'db'
];

```
### 配置数据表 
- 进入绝对路径`C:/Users/Micateam/Desktop/youloge/composer/public`目录下新增文件`youloge.sqlite.json`
-  `Sqlite 是以为文件作为数据库`，一个文件就是一个完整的数据库，你`必须做到`预先把数据表设计好：
- 支持复杂目录`www/hello/word.user` 点文件夹也支持* `.user` 别和`app.php > format` 重复防止`同名文件夹-文件名`
- 后续考虑`sqlite 配置`整合到`absolute 绝对路径里`去加载，可以更好的分布式共享：例如丢到`挂载盘共享`

```json
/**
 * 判断标准 键值为数组 键名代表目录名
 * 判断标准 键值为字符 键名代表数据库表名
 */
{
  "init":"id INT PRIMARY KEY AUTOINCREMENT,name varhcar(32),avatar varhcar(128),mail varhcar(128)",
  "test":"id INT PRIMARY KEY AUTOINCREMENT,name varhcar(32),avatar varhcar(128),mail varhcar(128)",
  "www.site":{
    "hello":"id INT PRIMARY KEY AUTOINCREMENT,avatar varhcar(128),mail varhcar(128),created text(12)",
    "word":"id INT PRIMARY KEY AUTOINCREMENT,name varhcar(32),avatar varhcar(128),mail varhcar(128)",
    "ok":{
      "no":"id INT PRIMARY KEY AUTOINCREMENT,state varhcar(128)",
    }
  },
  "open.site":{
    "info":"id INT PRIMARY KEY AUTOINCREMENT,state varhcar(128),created text(12)"
  }
}

```
> `youloge.sqlite.json` 文件数据表配置，要仔细：数据库表一旦建立了，修改更改表会十分的麻烦+蛋疼
- $db = sqlite('/','db001'); 会在`绝对路径`目录下新建一个`db001.db`文件，自动创建二张表`init`和`test`
- $db = sqlite('www.site/ok','db002'); 会在`绝对路径/www.site/ok`目录下新建一个`db002.db`文件，自动创建一张表`no`

### 开始使用 

#### 连接数据库
- 相对路径 目录`open.site` 文件`youloge` 表名`info` 为例
``` php
$db = sqlite('open.site','youloge');
sqlite::version(); // 静态调用
```
#### 插入数据 `$table, $data`
插入一条数据
``` php
$insert = sqlite->insert('info',['state'=>'正常']);
echo $insert; // 返回插入的行数 行数 ≠ 自增ID
```
插入多条数据
``` php
$insert = sqlite->insert('info',[['state'=>'正常'],['state'=>'正常'],['state'=>'正常']]);
echo $insert; // 返回插入的行数 行数 ≠ 自增ID
```
#### 更新数据 `$table, $data, $where`
一般更新
``` php
$update = sqlite->update('info',['state'=>'禁言'],['id'=>1]);
echo $update; // 返回1,0
```
特殊更新 
``` php
$update = sqlite->update('info',['state'=>'禁言'],['id > 5','state'=>'正常']);
echo $update; // 返回1,0
```
#### 删除数据 `$table,$where`

``` php
$delete = sqlite->delete('info',['state'=>'禁言']); // 键值对
$delete = sqlite->delete('info',['state IS NULL']); // 纯数组
echo $delete; // 返回1,0
```
#### 单条查询 `$table, $columns, $where` 可选`$order=false`
- row_array
``` php
$row_array = sqlite->row_array('info','*',['id'=>100]);
$row_array = sqlite->row_array('info',['*','id as uuid'],['id < 100','state'=>'正常'],'created desc');
echo $row_array; // []
```
#### 多条查询 `$table, $columns, $where,$limit=10,$offset=0,$order=false`
- result_array
``` php
$result_array = sqlite->result_array('info','*',['id'=>100],10,0);
echo $result_array; // []
```
#### 统计查询 `$table, $columns, $where,$limit=10,$offset=0,$order=false`
- 好用但是数据多了 这玩意肯定卡~
``` php
$count_array = sqlite->count_array('info','*',['id'=>100],10,0);
echo $count_array; // ['list'=>[],'count'=>0,'limit'=>10,'offset'=>0];
```
> 1.0.2 旧版是链式调用的：因为webman是常驻内存，类只加载一次，我写不好连接池所以2.0.0之后废弃链式调用

### 扩展函数 `-> 箭头调用`官方是`:: 静态调用`
- `insert` - 插入数据
- `update` - 更新数据
- `delete` - 删除数据
- `row_array` - 单条查询
- `result_array` - 多条查询
- `count_array` - 统计查询

### 原生连接句柄
```
$pdo = sqlite('hello/word','profile');
$pdo::exec();
$pdo::query();
$pdo::close();
$pdo::busyTimeout();
```

> 关于交流打赏：VX：`micateam`
