<?php
$installer = $this;
$installer->startSetup();

$installer->run("
    
    DROP TABLE IF EXISTS {$this->getTable('jirafe_analytics/queue_type')};
    CREATE TABLE {$this->getTable('jirafe_analytics/queue_type')} (
        `id` int(10) unsigned NOT NULL,
        `description` varchar(32),
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    
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
        `attempt_count` int(10) unsigned NOT NULL DEFAULT '0',
        `success` tinyint(1) unsigned NOT NULL DEFAULT '0',
        `created_dt` datetime DEFAULT NULL,
        `completed_dt` datetime DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `type_id` (`type_id`),
        CONSTRAINT `jirafe_analytics_queue_ibfk_1` FOREIGN KEY (`type_id`) REFERENCES `jirafe_analytics_queue_type` (`id`)
    ) ENGINE=InnoDB  AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
    
    DROP TABLE IF EXISTS {$this->getTable('jirafe_analytics/queue_attempt')};
    CREATE TABLE {$this->getTable('jirafe_analytics/queue_attempt')} (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `queue_id` int(10) unsigned NOT NULL,
        `http_code` int(10) unsigned NOT NULL,
        `total_time` FLOAT DEFAULT 0,
        `created_dt` datetime DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `queue_id` (`queue_id`),
        CONSTRAINT `jirafe_analytics_queue_attempt_ibfk_1` FOREIGN KEY (`queue_id`) REFERENCES `jirafe_analytics_queue` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
     
    DROP TABLE IF EXISTS {$this->getTable('jirafe_analytics/queue_error')};
    CREATE TABLE {$this->getTable('jirafe_analytics/queue_error')} (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `queue_id` int(10) unsigned NOT NULL,
        `queue_attempt_id` int(10) unsigned NOT NULL,
        `response` TEXT,
        `created_dt` datetime DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `queue_id` (`queue_id`),
        KEY `queue_attempt_id` (`queue_attempt_id`),
        CONSTRAINT `jirafe_analytics_queue_error_ibfk_1` FOREIGN KEY (`queue_id`) REFERENCES `jirafe_analytics_queue` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT `jirafe_analytics_queue_error_ibfk_2` FOREIGN KEY (`queue_attempt_id`) REFERENCES `jirafe_analytics_queue_attempt` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

");
$installer->endSetup();
