UPDATE          `onethink_config` SET `value`='1:基本\r 5:后台\r 2:内容\r 3:用户\r 4:系统' WHERE `name`='CONFIG_GROUP_LIST';
INSERT INTO     `onethink_config` VALUES (NULL, 'ADMIN_SITE_TITLE', '1', '后台标题', '5', 'OneThink管理平台', '后台网站的标题', '1387165685', '1387165685', '1', '', '0');
INSERT INTO     `onethink_config` VALUES (NULL, 'ADMIN_SITE_LOGO', '1', '后台LOGO', '5', '', '后台网站LOGO的URL', '1387165685', '1387165685', '1', '', '0');
INSERT INTO     `onethink_config` VALUES (NULL, 'ADMIN_LOGIN_LOGO', '1', '后台登录LOGO', '5', '', '后台登录页面LOGO的URL', '1387165685', '1387165685', '1', '', '0');
INSERT INTO     `onethink_config` VALUES (NULL, 'ADMIN_SHOW_COPYRIGHT', '4', '是否显示OneThink版权信息', '5', '0:关闭\r\n1:开启', '是否显示OneThink版权信息', '1383105995', '1383291877', '1', '1', '0');
