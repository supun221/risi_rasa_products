CREATE TABLE IF NOT EXISTS `lorry_stock` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stock_entry_id` int(11) NOT NULL,
  `rep_id` int(11) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `itemcode` varchar(50) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `barcode` varchar(255) DEFAULT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `date_added` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','sold','returned','damaged') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  KEY `stock_entry_id` (`stock_entry_id`),
  KEY `rep_id` (`rep_id`),
  KEY `barcode` (`barcode`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
