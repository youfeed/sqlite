<?php
/**
 * 要多看几遍 千万别配错了
 * 根目录/[动态文件名.后缀]: 数据库表名=>值为数据库字段
 * 根目录/子目录/[动态文件名.后缀]: 数据库表名=>值为数据库字段
 * 判断标准 键值为数组 键名代表目录名
 * 判断标准 键值为字符 键名代表数据库表名
 */
return [



  // 以下插件保留目录: 方便以后扩展使用 
  // :memory: 内存数据库
  'logs'=>[
    'error'=>'id INTEGER AUTOINCREMENT,sql text(500),status varhcar(24),created text(12)',
  ],
  ':memory'=>[]
];

// // 复杂结构参考 
// return [
//   // 数据表
//   'config'=>'Sql表结构',
//   'online'=>'Sql表结构',
//   'office'=>'Sql表结构',
//   // 用户目录
//   'profile'=>[
//     'user'=>'Sql表结构',
//     'wallet'=>'Sql表结构',
//     'black'=>'Sql表结构',
//     // 管理员目录
//     'admin'=>[
//       'super'=>'Sql表结构',
//       'admin'=>'Sql表结构',
//     ]
//   ],
//   // 插件保留目录 为了以后扩展使用 :memory: 内存数据库
//   'logs'=>[
//     'error'=>'id INTEGER AUTOINCREMENT,sql text(500),status varhcar(24),created text(12)',
//   ],
// ];