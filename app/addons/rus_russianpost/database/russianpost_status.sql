DROP TABLE IF EXISTS `?:rus_russianpost_status`;
CREATE TABLE IF NOT EXISTS `?:rus_russianpost_status` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` mediumint(8) NOT NULL DEFAULT '0',
  `shipment_id` mediumint(8) NOT NULL DEFAULT '0',
  `tracking_number` varchar(255) NOT NULL,
  `timestamp` int(11) unsigned NOT NULL DEFAULT '0',
  `address` varchar(256) NOT NULL,
  `type_operation` varchar(100) NOT NULL,
  `status` varchar(150) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
