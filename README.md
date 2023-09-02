# Youloge.Sqlite Webman 基础插件

### 项目介绍

Sqlite3 插件：它是对标`fopen 函数`的，性能还是不错了，像一般性的个人博客，企业官网，完全都是可以hold住的。
其中`Sqlite 的内存型`反应时间是`ns`级别的，其他数据库都会有网络开销可以做到`ms`级别。

### 项目地址

[Github Youloge.sqlite](https://github.com/youloge/youloge.sqlite) Star我 `我们一起做大做强`

### 安装

```php
composer require youloge/sqlite
```
> 到 app/functions.php 添加一个助手函数
``` php  
use Youloge\Sqlite\Sqlite;     
if(!function_exists('sqlite')){                  
  function sqlite($dir,$file,$table){                     
    return new Sqlite($dir,$file,$table);               
  }    
}
// 其他地方 
$db = sqlite('','',''); // 这样子使用 wenbman的助手函数是个好东西
```

### 配置文件 `Sqlite 没什么远程管理工具 配置文件是关键的关键`

位置：config/plugin/youloge/app.php

```php
<?php
return [
    'enable' => true,
    // 绝对路径 通过`__FILE__ ` 查看系统目录，放到一个你觉得合适的位置 **不要公开**
    'absolute'=>'C:/Users/Micateam/Desktop/youloge/composer/public',
    // 文件后缀 你改成mp4都没问题 Sqlite3 能识别
    'format'=>'db'
];

```
位置：config/plugin/youloge/sqlite.php
```php
/**
 * 判断标准 键值为数组 键名代表目录名
 * 判断标准 键值为字符 键名代表数据库表名
 */
return [
  'init'=>'uuid text(64) primary key,name varhcar(32),avatar varhcar(128),mail varhcar(128),created text(12),updated text(12)',
  // 这个文件夹预插件保留 为以后扩展做准备 你可以使用`logs`文件夹的子目录
  'logs'=>[
    'info'=>'uuid text(64) primary key,name varhcar(32),avatar varhcar(128),mail varhcar(128),created text(12),updated text(12)',
  ],
];
```

### 配置数据表 关键

>  `Sqlite 是以为文件作为数据库`，一个文件就是一个完整的数据库，你`必须做到`预先把数据表设计好：

例子：在项目目录下建立用户表
```sql
uuid text(64) primary key,name varhcar(32),avatar varhcar(128),mail varhcar(128),created text(12),updated text(12)
```
- 

### 开始使用 
#### 以相对路径 目录`hello/word` 文件`profile` 表名`user,login，wallet` 为例

连接数据库

```
// 必须与config/plugin/youloge/sqlite.php配置文件对应
$db = sqlite('目录/目录','文件名','表名');
```
#### 插入数据
插入一条数据
``` php
$insert = sqlite('hello/word','profile','user')
          ->set(['id'=>100,'name'=>'name'])
          ->insert();
echo $insert; // 返回插入的行数 行数 ≠ 自增ID
```
插入多条数据
``` php
$insert = sqlite('hello/word','profile','user')
          ->set(
            [
              ['id'=>100,'name'=>'name'],
              ['id'=>101,'name'=>'name'],
              ['id'=>102,'name'=>'name']
            ]
          )
          ->insert(true);
echo $insert; // insert(true) 返回的是插入语句：专门用来检查语句的。
```
#### 更新数据
一般更新
``` php
$update = sqlite('hello/word','profile','user')
          ->set(['name'=>'newname'])
          ->where(['id'=>100])
          ->update();
echo $update; // 返回1,0
```
特殊更新 - 更新`name`为`null`的行
``` php
$update = sqlite('hello/word','profile','user')
          ->set(['name'=>'hello'])
          ->where([],['name is null']) // where 注意第一个参数
          ->update();
echo $update; // 返回1,0
```
#### 删除数据
一般更新
``` php
$delete = sqlite('hello/word','profile','user')
          ->where(['id'=>100])
          ->delete();
echo $delete; // 返回1,0
```
#### 查询数据
单条查询 - 
``` php
$row_array = sqlite('hello/word','profile','user')
          ->where(['id'=>100])
          ->row_array();
echo $row_array; // []
```
多条查询 - 
``` php
$result_array = sqlite('hello/word','profile','user')
          ->where(['id'=>100])
          ->result_array();
echo $result_array; // []
```
统计查询 - 好用但是数据多了 这玩意肯定卡~
``` php
$count_array = sqlite('hello/word','profile','user')
          ->where(['id'=>100])
          ->count_array();
echo $count_array; // ['list'=>[],'count'=>0];
```

### 链式调用支持的选项

- `limit` - 条数默认 10  `row_array`时不生效
- `offset` - 偏移量 默认 0
- `where` - [参数一],[参数二] 参数一：转成 `key`=`val`在`AND`,参数二：直接`AND`
- `set` - 设置数据[] `insert` 解析成 SET`keys`VALUE`values` `update`解析成``key`=`val``

> 链式调用相同配置，只有最后一组生效 $db()->limit(50)->limit(40)->(20)->result_array(); // 只生效limit(20)

### 调用结果函数
- `insert` - 插入数据
- `update` - 更新数据
- `delete` - 删除数据
- `row_array` - 单条查询
- `result_array` - 多条查询
- `count_array` - 统计查询

> 结果函数最后调用 配置`true`参数，代表返回`sqlite`语句


### 数据库连接句柄
```
$pdo = sqlite('hello/word','profile','user')->PDO();
$pdo->exec();
$pdo->close(); // 可以不要
```
> 可以不用关闭，调用结束插件自己会销毁
