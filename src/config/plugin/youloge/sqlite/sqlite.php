<?php
/**
 * 要多看几遍 千万别配错了
 * init 在根目录下 代表 [动态文件]数据库表名 值为数据库字段
 * table 代表目录 
 * hello 代码table目录下 [动态文件]的数据库表名 值为数据库字段
 * main 也代表目录
 * info 代码main目录下 的数据库表名 值为数据库字段
 * 判断标准 键值为数组 键名代表目录名
 * 判断标准 键值为字符 键名代表数据库表名
 */
return [
  'init'=>'uuid text(64) primary key,name varhcar(32),avatar varhcar(128),mail varhcar(128),created text(12),updated text(12)',
  'table'=>[
    'hello'=>'uuid text(64) primary key,name varhcar(32),avatar varhcar(128),mail varhcar(128),created text(12),updated text(12)',
    'main'=>[
      'info'=>'uuid text(64) primary key,name varhcar(32),avatar varhcar(128),mail varhcar(128),created text(12),updated text(12)',
      'info2'=>'uuid text(64) primary key,name varhcar(32),avatar varhcar(128),mail varhcar(128),created text(12),updated text(12)',
      'foot'=>[
        'hello'=>'uuid text(64) primary key,name varhcar(32),avatar varhcar(128),mail varhcar(128),created text(12),updated text(12)',
      ]
    ]
  ],
];
