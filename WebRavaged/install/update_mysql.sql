ALTER TABLE `{dbp}servers`
    ADD `log_url` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '';

CREATE TABLE IF NOT EXISTS `{dbp}leaderboards` (
  `guid` int(10) unsigned NOT NULL DEFAULT '0',
  `sid` int(10) unsigned NOT NULL DEFAULT '0',
  `kills` int(10) unsigned NOT NULL DEFAULT '0',
  `deaths` int(10) unsigned NOT NULL DEFAULT '0',
  `headshots` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (guid, sid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;