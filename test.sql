-- 测试用数据库。

create table `coco_sys_user` (
  `user_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_name` varchar(255) NOT NULL DEFAULT '' COMMENT '用户名称(登录名)',
  `user_abstruct` varchar(255) NOT NULL DEFAULT '' COMMENT '用户简介',
  `user_badge` varchar(255) NOT NULL DEFAULT '' COMMENT '用户的图片类标识(头像)',
  `user_password` varchar(255) NOT NULL DEFAULT '' COMMENT '密码',
  `user_salt` varchar(255) NOT NULL DEFAULT '' COMMENT '密码盐(用于混淆hash后的密码)',
  `is_del` tinyint(2) NOT NULL DEFAULT 1 COMMENT '是否删除 1未删除 2已删除 3 冻结',
  `creator_id` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '创造者id',
  `modifier_id` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '最后修改者id',
  `create_time` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '创建时间',
  `modify_time` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '最后修改时间',
  PRIMARY KEY (`user_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='系统用户表';

-- 默认可登录账密 admin / admin123
INSERT INTO `test`.`coco_sys_user`( `user_name`, `user_abstruct`, `user_badge`, `user_password`, `user_salt`, `is_del`, `main_id`, `creator_id`, `modifier_id`, `create_time`, `modify_time`) VALUES ( 'admin', 'emmmmmmdd', 'https://gw.alipayobjects.com/zos/rmsportal/WdGqmHpayyMjiEhcKoVE.png', 'f43b669975b3a4cd9af5f15893bd455e', '1585466179', 1, 0, 0, 1, 0, 1585471670);
