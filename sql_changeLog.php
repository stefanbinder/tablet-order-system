

#### Categories - Änderung für Verwaltung

ALTER TABLE `categories` ADD `left` BIGINT( 20 ) NOT NULL DEFAULT '0',
ADD `right` BIGINT( 20 ) NOT NULL DEFAULT '0',
ADD `position` BIGINT( 20 ) NOT NULL DEFAULT '0',
ADD `level` BIGINT( 20 ) NOT NULL DEFAULT '0'
ADD `type` TEXT NOT NULL DEFAULT 'folder'