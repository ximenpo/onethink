UPDATE          `onethink_config` SET `value`='1:基本\r\n2:内容\r\n3:用户\r\n5:后台\r\n4:系统' WHERE `name`='CONFIG_GROUP_LIST';
INSERT INTO     `onethink_config` VALUES (NULL, 'ADMIN_SITE_TITLE', '1', '后台标题', '5', '', '后台网站的标题', '1387165685', '1387165685', '1', 'OneThink管理平台', '0');
INSERT INTO     `onethink_config` VALUES (NULL, 'ADMIN_SITE_LOGO', '1', '后台LOGO', '5', '', '后台网站LOGO的URL', '1387165685', '1387165685', '1', '', '0');
INSERT INTO     `onethink_config` VALUES (NULL, 'ADMIN_LOGIN_LOGO', '1', '后台登录LOGO', '5', '', '后台登录页面LOGO的URL', '1387165685', '1387165685', '1', '', '0');
INSERT INTO     `onethink_config` VALUES (NULL, 'ADMIN_LOGIN_EXTRA_VERIFY', '4', '后台登录附加验证', '5', '0:无\r\n1:图片验证码\r\n2:动态验证码（Google Authenticator）', '后台登录使用的附加验证方式', '1383105995', '1383291877', '1', '1', '0');
INSERT INTO     `onethink_config` VALUES (NULL, 'ADMIN_SHOW_COPYRIGHT', '4', '是否显示OneThink版权信息', '5', '0:关闭\r\n1:开启', '是否显示OneThink版权信息', '1383105995', '1383291877', '1', '1', '0');

ALTER TABLE     `onethink_ucenter_member` ADD COLUMN `tfa_seed` VARCHAR(32) NOT NULL DEFAULT '' COMMENT 'two-factor authentication seed' AFTER `mobile`;
