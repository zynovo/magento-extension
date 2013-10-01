<?php
$installer = $this;
$installer->startSetup();

$installer->run("
    
    DROP TABLE IF EXISTS {$this->getTable('jirafe_analytics/queue_type')};
    CREATE TABLE {$this->getTable('jirafe_analytics/queue_type')} (
        `id` int(10) unsigned NOT NULL,
        `description` varchar(32),
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
    
    INSERT INTO {$this->getTable('jirafe_analytics/queue_type')} VALUES
        (1, 'cart'),
        (2, 'category'),
        (3, 'customer'),
        (4, 'order'),
        (5, 'product'),
        (6, 'user');
        
    DROP TABLE IF EXISTS {$this->getTable('jirafe_analytics/queue')};
    CREATE TABLE {$this->getTable('jirafe_analytics/queue')} (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `content` text NOT NULL,
        `type_id` int(10) unsigned NOT NULL,
        `status_id` int(11) unsigned NOT NULL DEFAULT '0',
        `created_dt` datetime NOT NULL,
        `completed_dt` datetime DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `type_id` (`type_id`),
        CONSTRAINT `jirafe_analytics_queue_ibfk_1` FOREIGN KEY (`type_id`) REFERENCES `jirafe_analytics_queue_type` (`id`)
    ) ENGINE=InnoDB  AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
    
    DROP TABLE IF EXISTS {$this->getTable('jirafe_analytics/queue_attempt')};
    CREATE TABLE {$this->getTable('jirafe_analytics/queue_attempt')} (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `queue_id` int(10) unsigned NOT NULL,
        `response` text,
        `status_id` int(10) DEFAULT NULL,
        `created_dt` datetime NOT NULL,
        PRIMARY KEY (`id`),
        KEY `queue_id` (`queue_id`),
        CONSTRAINT `jirafe_analytics_queue_attempt_ibfk_1` FOREIGN KEY (`queue_id`) REFERENCES `jirafe_analytics_queue` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
     
  

");
$installer->endSetup();
