--管理员表
create table `poll_manager`(
`id` int auto_increment,
`username` char(32) not null comment '帐号',
`password` char(32) not null comment '密码',
primary key(`id`)
)engine=innodb charset=utf8;

--问卷表
create table `poll_poll`(
`id` int auto_increment,
`title` char(128) not null comment '问卷标题',
`desc` text comment '问卷说明',
`desc_position` tinyint(1) not null default 1 comment '说明位置 1 顶部 2 底部',
`is_on` tinyint(1) unsigned not null default 1 comment '该投票是否开始 0为暂停 1 为正在开放  2为已关闭 3为还未开始',
`select_num_day`  tinyint(1) unsigned not null comment '每天用户问卷次数',
`start_time`  int(11) unsigned not null comment '开始时间',
`end_time`  int(11) unsigned not null comment '结束时间',
primary key(`id`)
)engine=innodb charset=utf8;

--问卷问题表
create table `poll_poll_question`(
`id` int unsigned auto_increment,
`poll_id` int unsigned not null comment '问卷表id',
`is_must` tinyint(1) unsigned  default 0 comment '是否必答 0 否 1 是',
`question_sort_num` tinyint(1) unsigned not null comment '排序值 从小到大 排序',
`question_title` char(255) not null comment '问题标题',
`question_type` tinyint(1) unsigned not null comment '问卷类型 1 单选 2 多选 3 填空 4 文件上传',
`select_num`  tinyint(1) unsigned default 0 comment '多选最大数量 0为无限 只有多选有效',
`user_select_num` int(11) unsigned not null default 0 comment '用户投票总数量 用于计算',
primary key(`id`),
index (`poll_id`)
)engine=innodb charset=utf8;

--问卷问题选项表
create table `poll_poll_option`(
`id` int auto_increment,
`poll_id` int unsigned not null comment '问卷表id',
`question_id` int unsigned not null comment '问题表id',
`option_sort_num` tinyint(1) unsigned not null comment '排序值 从小到大 排序',
`is_default` tinyint(1) unsigned default 0 comment '是否默认选中 只有单选多选有效 0 否 1 是',
`upload_minetype` char(128) default '' comment '上传文件允许的类型 以逗号分隔 只有文件上传有效',
`option_name` char(128) default '' comment '选项名称',
`option_img` char(255) default '' comment '选项图片',
`num`  int(11) unsigned not null default 0  comment '投票数量 只有单选多选有效 用于统计',
primary key(`id`),
index poll_id (`poll_id`)
)engine=innodb charset=utf8;

--用户问卷作答记录表
create table `poll_user_history`(
`id` int auto_increment,
`user_id` int(11) not null comment '用户id',
`poll_id` int(11) not null comment '问卷表id',
`create_time` int(11) not null comment '创建时间',
primary key(`id`)
)engine=innodb charset=utf8;

--用户表
create table `poll_user`(
`id` int auto_increment,
`open_id` char(32) not null comment 'openid',
primary key(`id`)
)engine=innodb charset=utf8;

--填空题用户填写表
create table `poll_user_input_answer`(
`id` int auto_increment,
`user_id` int(11) not null comment '用户id',
`option_id` int(11) not null comment '选项id',
`answer` varchar(999) not null comment '用户文本回答',
primary key(`id`),
index (`option_id`)
)engine=innodb charset=utf8;

--上传文件用户填写表
create table `poll_user_file_upload`(
`id` int auto_increment,
`user_id` int(11) not null comment '用户id',
`option_id` int(11) not null comment '选项id',
`file_url` varchar(256) not null comment '文件路径',
primary key(`id`),
index (`option_id`)
)engine=innodb charset=utf8;