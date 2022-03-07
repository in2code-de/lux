-- noinspection SqlNoDataSourceInspectionForFile
-- noinspection SqlDialectInspectionForFile

CREATE TABLE tx_lux_domain_model_visitor (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	pagevisits int(11) DEFAULT '0' NOT NULL,
	newsvisits int(11) DEFAULT '0' NOT NULL,
	linkclicks int(11) DEFAULT '0' NOT NULL,
	categoryscorings int(11) DEFAULT '0' NOT NULL,
	attributes int(11) DEFAULT '0' NOT NULL,
	ipinformations int(11) DEFAULT '0' NOT NULL,
	downloads int(11) DEFAULT '0' NOT NULL,
	logs int(11) DEFAULT '0' NOT NULL,
	frontenduser int(11) DEFAULT '0' NOT NULL,

	identified tinyint(4) unsigned DEFAULT '0' NOT NULL,
	blacklisted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	email varchar(255) DEFAULT '' NOT NULL,
	fingerprints varchar(255) DEFAULT '' NOT NULL,
	ip_address varchar(255) DEFAULT '' NOT NULL,
	visits int(11) unsigned DEFAULT '0' NOT NULL,
	scoring int(11) unsigned DEFAULT '0' NOT NULL,
	description text NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY email (email(20)),
	KEY frontenduser (frontenduser),
	KEY fingerprints (fingerprints(20)),
	KEY scoring (scoring),
	KEY language (l10n_parent,sys_language_uid)
);

CREATE TABLE tx_lux_domain_model_fingerprint (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	value varchar(255) DEFAULT '' NOT NULL,
	domain varchar(255) DEFAULT '' NOT NULL,
	user_agent varchar(255) DEFAULT '' NOT NULL,
	type int(11) unsigned DEFAULT '0' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY value (value(33)),
	KEY domain (domain(50)),
	KEY user_agent (user_agent(50)),
	KEY type (type),
	KEY language (l10n_parent,sys_language_uid)
);

CREATE TABLE tx_lux_domain_model_attribute (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	visitor int(11) DEFAULT '0' NOT NULL,

	name varchar(255) DEFAULT '' NOT NULL,
	value varchar(255) DEFAULT '' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY visitor (visitor),
	KEY name (name(20)),
	KEY value (value(20)),
	KEY language (l10n_parent,sys_language_uid)
);

CREATE TABLE tx_lux_domain_model_pagevisit (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	visitor int(11) DEFAULT '0' NOT NULL,

	page int(11) DEFAULT '0' NOT NULL,
	language int(11) DEFAULT '0' NOT NULL,
	referrer text DEFAULT '' NOT NULL,
	domain varchar(255) DEFAULT '' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY visitor (visitor),
	KEY page (page),
	KEY referrer (referrer(50)),
	KEY domain (domain(50)),
	KEY language (l10n_parent,sys_language_uid)
);

CREATE TABLE tx_lux_domain_model_newsvisit (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	visitor int(11) DEFAULT '0' NOT NULL,

	news int(11) DEFAULT '0' NOT NULL,
	pagevisit int(11) DEFAULT '0' NOT NULL,
	language int(11) DEFAULT '0' NOT NULL,
	domain varchar(255) DEFAULT '' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY visitor (visitor),
	KEY news (news),
	KEY pagevisit (pagevisit),
	KEY domain (domain(50)),
	KEY language (l10n_parent,sys_language_uid)
);

CREATE TABLE tx_lux_domain_model_download (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	visitor int(11) DEFAULT '0' NOT NULL,
	file int(11) DEFAULT '0' NOT NULL,

	href varchar(255) DEFAULT '' NOT NULL,
	page int(11) DEFAULT '0' NOT NULL,
	domain varchar(255) DEFAULT '' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY visitor (visitor),
	KEY file (file),
	KEY href (href(50)),
	KEY page (page),
	KEY domain (domain(50)),
	KEY language (l10n_parent,sys_language_uid)
);

CREATE TABLE tx_lux_domain_model_ipinformation (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	visitor int(11) DEFAULT '0' NOT NULL,

	name varchar(255) DEFAULT '' NOT NULL,
	value varchar(255) DEFAULT '' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY visitor (visitor),
	KEY name (name(20)),
	KEY value (value(50)),
	KEY language (l10n_parent,sys_language_uid)
);

CREATE TABLE tx_lux_domain_model_search (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	visitor int(11) unsigned DEFAULT '0' NOT NULL,

	searchterm varchar(255) DEFAULT '' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY visitor (visitor),
	KEY searchterm (searchterm(20)),
	KEY language (l10n_parent,sys_language_uid)
);

CREATE TABLE tx_lux_domain_model_linklistener (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	linkclicks int(11) DEFAULT '0' NOT NULL,

	title varchar(255) DEFAULT '' NOT NULL,
	description text NOT NULL,
	link varchar(255) DEFAULT '' NOT NULL,
	category int(11) DEFAULT '0' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY linkclicks (linkclicks),
	KEY category (category),
	KEY language (l10n_parent,sys_language_uid)
);

CREATE TABLE tx_lux_domain_model_categoryscoring (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	visitor int(11) unsigned DEFAULT '0' NOT NULL,

	category int(11) unsigned DEFAULT '0' NOT NULL,
	scoring int(11) unsigned DEFAULT '0' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY visitor (visitor),
	KEY category (category),
	KEY language (l10n_parent,sys_language_uid)
);

CREATE TABLE tx_lux_domain_model_linkclick (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	visitor int(11) unsigned DEFAULT '0' NOT NULL,

	page int(11) DEFAULT '0' NOT NULL,
	linklistener int(11) DEFAULT '0' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY visitor (visitor),
	KEY page (page),
	KEY linklistener (linklistener),
	KEY language (l10n_parent,sys_language_uid)
);

CREATE TABLE tx_lux_redirect (
	uid int(11) NOT NULL auto_increment,

	target varchar(255) DEFAULT '' NOT NULL,
	hash varchar(255) DEFAULT '' NOT NULL,
	arguments text NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid)
);

CREATE TABLE tx_lux_domain_model_log (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	visitor int(11) DEFAULT '0' NOT NULL,

	status tinyint(4) unsigned DEFAULT '0' NOT NULL,
	properties text NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY visitor (visitor),
	KEY status (status),
	KEY properties (properties(80)),
	KEY language (l10n_parent,sys_language_uid)
);

CREATE TABLE sys_category (
	lux_category tinyint(4) unsigned DEFAULT '0' NOT NULL
);
