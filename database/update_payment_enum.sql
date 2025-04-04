-- Alter the rep_payments table to add 'cheque' to payment_method enum
ALTER TABLE `rep_payments` 
MODIFY COLUMN `payment_method` enum('cash','card','credit_card','credit','advance','cheque') NOT NULL;
