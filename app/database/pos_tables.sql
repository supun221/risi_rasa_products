-- Create sales transactions table
CREATE TABLE IF NOT EXISTS `pos_sales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(50) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `net_amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','card','credit_card','credit','advance') NOT NULL,
  `paid_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `change_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `credit_amount` decimal(10,2) DEFAULT 0.00,
  `advance_used` decimal(10,2) DEFAULT 0.00,
  `rep_id` int(11) NOT NULL,
  `sale_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL DEFAULT 'completed',
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_number` (`invoice_number`),
  KEY `customer_id` (`customer_id`),
  KEY `rep_id` (`rep_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create sales items table
CREATE TABLE IF NOT EXISTS `pos_sale_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sale_id` int(11) NOT NULL,
  `lorry_stock_id` int(11) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL,
  `free_quantity` int(11) NOT NULL DEFAULT 0,
  `unit_price` decimal(10,2) NOT NULL,
  `discount_percent` decimal(5,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sale_id` (`sale_id`),
  KEY `lorry_stock_id` (`lorry_stock_id`),
  CONSTRAINT `fk_sale_items_sale` FOREIGN KEY (`sale_id`) REFERENCES `pos_sales` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create advance payments table
CREATE TABLE IF NOT EXISTS `advance_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `payment_type` enum('cash','card','bank') NOT NULL,
  `reason` text DEFAULT NULL,
  `net_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `print_bill` tinyint(1) NOT NULL DEFAULT 0,
  `advance_bill_number` varchar(50) NOT NULL,
  `branch` varchar(100) NOT NULL DEFAULT 'main',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `advance_bill_number` (`advance_bill_number`),
  CONSTRAINT `fk_advance_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create customer payments table
CREATE TABLE IF NOT EXISTS `customer_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `invoice_number` varchar(50) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(20) NOT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `invoice_number` (`invoice_number`),
  CONSTRAINT `fk_payment_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add credit balance column to customers table
ALTER TABLE `customers` 
ADD COLUMN IF NOT EXISTS `credit_balance` decimal(10,2) NOT NULL DEFAULT 0.00,
ADD COLUMN IF NOT EXISTS `last_purchase_date` datetime DEFAULT NULL;

-- Add advance_amount column to customers table if not exists
ALTER TABLE `customers` 
ADD COLUMN IF NOT EXISTS `advance_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
ADD COLUMN IF NOT EXISTS `credit_balance` decimal(10,2) NOT NULL DEFAULT 0.00;

-- Lorry stock table definition
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

-- Create lorry transactions table to track stock movement
CREATE TABLE IF NOT EXISTS `lorry_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stock_entry_id` int(11) NOT NULL,
  `rep_id` int(11) NOT NULL,
  `transaction_type` enum('add','retrieve','transfer','return') NOT NULL,
  `quantity` int(11) NOT NULL,
  `reason` varchar(50) DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `barcode` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `transaction_date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `stock_entry_id` (`stock_entry_id`),
  KEY `rep_id` (`rep_id`),
  KEY `transaction_type` (`transaction_type`),
  KEY `transaction_date` (`transaction_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create rep_payments table to track payment transactions
CREATE TABLE IF NOT EXISTS `rep_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(50) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','card','credit_card','credit','advance') NOT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `rep_id` int(11) NOT NULL,
  `branch` varchar(100) NOT NULL DEFAULT 'main',
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `invoice_number` (`invoice_number`),
  KEY `customer_id` (`customer_id`),
  KEY `rep_id` (`rep_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add cheque_num column to rep_payments table
ALTER TABLE `rep_payments` 
ADD COLUMN `cheque_num` VARCHAR(50) DEFAULT NULL AFTER `payment_method`;

-- Add cheque_number column to pos_sales table if it doesn't exist
ALTER TABLE `pos_sales` 
ADD COLUMN `cheque_number` VARCHAR(50) DEFAULT NULL AFTER `payment_method`;