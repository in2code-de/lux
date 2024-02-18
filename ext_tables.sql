-- noinspection SqlNoDataSourceInspectionForFile
-- noinspection SqlDialectInspectionForFile

CREATE TABLE tx_lux_domain_model_visitor (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	companyrecord int(11) DEFAULT '0' NOT NULL,
	pagevisits int(11) DEFAULT '0' NOT NULL,
	newsvisits int(11) DEFAULT '0' NOT NULL,
	linkclicks int(11) DEFAULT '0' NOT NULL,
	categoryscorings int(11) DEFAULT '0' NOT NULL,
	attributes int(11) DEFAULT '0' NOT NULL,
	ipinformations int(11) DEFAULT '0' NOT NULL,
	downloads int(11) DEFAULT '0' NOT NULL,
	logs int(11) DEFAULT '0' NOT NULL,
	frontenduser int(11) DEFAULT '0' NOT NULL,
	fingerprints varchar(255) DEFAULT '' NOT NULL,

	identified tinyint(4) unsigned DEFAULT '0' NOT NULL,
	blacklisted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	email varchar(255) DEFAULT '' NOT NULL,
	company varchar(255) DEFAULT '' NOT NULL,
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
	KEY frontenduser (frontenduser),
	KEY fingerprints (fingerprints(20)),
	KEY identified (identified),
	KEY blacklisted (blacklisted),
	KEY email (email(20)),
	KEY company (company(20)),
	KEY companyrecord (companyrecord),
	KEY scoring (scoring),
	KEY description (description(30)),
	KEY tstamp (tstamp),
	KEY crdate (crdate),
	KEY deleted (deleted),
	KEY hidden (hidden),
	KEY language (l10n_parent,sys_language_uid)
);

CREATE TABLE tx_lux_domain_model_fingerprint (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	value varchar(255) DEFAULT '' NOT NULL,
	domain varchar(255) DEFAULT '' NOT NULL,
	user_agent varchar(512) DEFAULT '' NOT NULL,
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
	KEY crdate (crdate),
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
	KEY language_lux (language),
	KEY referrer (referrer(50)),
	KEY domain (domain(50)),
	KEY crdate (crdate),
	KEY language (l10n_parent,sys_language_uid)
);

CREATE TABLE tx_lux_domain_model_individualvisit (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	visitor int(11) DEFAULT '0' NOT NULL,

	identifier_foreign int(11) DEFAULT '0' NOT NULL,
	table_foreign varchar(255) DEFAULT '' NOT NULL,
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
	KEY identifier (identifier),
	KEY table (table),
	KEY pagevisit (pagevisit),
	KEY languagelux (language),
	KEY domain (domain(50)),
	KEY crdate (crdate),
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
	KEY languagelux (language),
	KEY domain (domain(50)),
	KEY crdate (crdate),
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
	KEY crdate (crdate),
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
	KEY crdate (crdate),
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
	KEY crdate (crdate),
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
	KEY title (title(30)),
	KEY description (description(50)),
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
	KEY crdate (crdate),
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

CREATE TABLE tx_lux_domain_model_utm (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	pagevisit int(11) DEFAULT '0' NOT NULL,
	newsvisit int(11) DEFAULT '0' NOT NULL,

	utm_source varchar(255) DEFAULT '' NOT NULL,
	utm_medium varchar(255) DEFAULT '' NOT NULL,
	utm_campaign varchar(255) DEFAULT '' NOT NULL,
	utm_id varchar(255) DEFAULT '' NOT NULL,
	utm_term varchar(255) DEFAULT '' NOT NULL,
	utm_content varchar(255) DEFAULT '' NOT NULL,
	referrer text DEFAULT '' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY pagevisit (pagevisit),
	KEY newsvisit (newsvisit),
	KEY utm_source (utm_source(30)),
	KEY utm_medium (utm_medium(30)),
	KEY utm_campaign (utm_campaign(30)),
	KEY utm_id (utm_id(10)),
	KEY utm_term (utm_term(30)),
	KEY utm_content (utm_content(30)),
	KEY crdate (crdate),
	KEY language (l10n_parent,sys_language_uid)
);

CREATE TABLE tx_lux_domain_model_company (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	category int(11) unsigned DEFAULT '0' NOT NULL,

	title varchar(255) DEFAULT '' NOT NULL,
	branch_code varchar(255) DEFAULT '' NOT NULL,
	city varchar(255) DEFAULT '' NOT NULL,
	contacts text DEFAULT '' NOT NULL,
	continent varchar(255) DEFAULT '' NOT NULL,
	country_code varchar(255) DEFAULT '' NOT NULL,
	region varchar(255) DEFAULT '' NOT NULL,
	street varchar(255) DEFAULT '' NOT NULL,
	zip varchar(255) DEFAULT '' NOT NULL,
	domain varchar(255) DEFAULT '' NOT NULL,
	founding_year varchar(255) DEFAULT '' NOT NULL,
	phone varchar(255) DEFAULT '' NOT NULL,
	revenue varchar(255) DEFAULT '' NOT NULL,
	revenue_class varchar(255) DEFAULT '' NOT NULL,
	size varchar(255) DEFAULT '' NOT NULL,
	size_class varchar(255) DEFAULT '' NOT NULL,
	description text NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY category (category),
	KEY language (l10n_parent,sys_language_uid)
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
	KEY crdate (crdate),
	KEY language (l10n_parent,sys_language_uid)
);

CREATE TABLE fe_users (
	KEY email (email(30))
);

CREATE TABLE sys_category (
	lux_category tinyint(4) unsigned DEFAULT '0' NOT NULL,
	lux_company_category tinyint(4) unsigned DEFAULT '0' NOT NULL,

	KEY deleted (deleted),
	KEY lux_category (lux_category),
	KEY title (title(20))
);

CREATE TABLE sys_category_record_mm (
	KEY tablenames (tablenames(30)),
	KEY fieldname (fieldname(20))
);

CREATE TABLE sys_file (
	KEY name (name(30))
);
