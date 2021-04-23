
DROP TABLE IF EXISTS `?:rus_commerceml_currencies`;
CREATE TABLE IF NOT EXISTS `?:rus_commerceml_currencies` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `currency_id` mediumint(8) NOT NULL DEFAULT '0',
  `commerceml_currency` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
