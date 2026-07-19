SET FOREIGN_KEY_CHECKS=0;

-- ==========================================
-- 1. FOUNDATION & HIERARCHY
-- ==========================================

-- Companies
CREATE TABLE `companies` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `uuid` CHAR(36) UNIQUE NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `legal_name` VARCHAR(200),
  `gstin` VARCHAR(15),
  `pan` VARCHAR(10),
  `cin` VARCHAR(25),
  `address` TEXT,
  `city` VARCHAR(100),
  `state` VARCHAR(100),
  `pincode` VARCHAR(10),
  `phone` VARCHAR(20),
  `email` VARCHAR(100),
  `logo_path` VARCHAR(255),
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Branches
CREATE TABLE `branches` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `uuid` CHAR(36) UNIQUE NOT NULL,
  `company_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `code` VARCHAR(20) UNIQUE NOT NULL,
  `type` ENUM('head_office','regional','zone','depot') DEFAULT 'regional',
  `address` TEXT,
  `city` VARCHAR(100),
  `state` VARCHAR(100),
  `pincode` VARCHAR(10),
  `phone` VARCHAR(20),
  `manager_user_id` INT UNSIGNED NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_branches_company` (`company_id`),
  FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
  -- manager_user_id FK added at the end
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Warehouses
CREATE TABLE `warehouses` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `uuid` CHAR(36) UNIQUE NOT NULL,
  `company_id` INT UNSIGNED NOT NULL,
  `branch_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `code` VARCHAR(20) UNIQUE NOT NULL,
  `type` ENUM('central','sub','cnf','van') DEFAULT 'sub',
  `address` TEXT,
  `manager_user_id` INT UNSIGNED NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_warehouses_branch` (`branch_id`),
  FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
  -- manager_user_id FK added at the end
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Financial Years
CREATE TABLE `financial_years` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `company_id` INT UNSIGNED NOT NULL,
  `label` VARCHAR(20) NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `is_current` TINYINT(1) DEFAULT 0,
  `is_locked` TINYINT(1) DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tax Slabs
CREATE TABLE `tax_slabs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `company_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(50) NOT NULL,
  `tax_type` ENUM('GST','VAT','IGST','NONE') DEFAULT 'GST',
  `cgst_rate` DECIMAL(5,2) DEFAULT 0.00,
  `sgst_rate` DECIMAL(5,2) DEFAULT 0.00,
  `igst_rate` DECIMAL(5,2) DEFAULT 0.00,
  `cess_rate` DECIMAL(5,2) DEFAULT 0.00,
  `effective_from` DATE NOT NULL,
  `effective_to` DATE NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TDS Rules
CREATE TABLE `tds_rules` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `company_id` INT UNSIGNED NOT NULL,
  `section_code` VARCHAR(10) NOT NULL,
  `description` VARCHAR(200),
  `rate_percent` DECIMAL(5,2) NOT NULL,
  `threshold_amount` DECIMAL(12,2) DEFAULT 0.00,
  `effective_from` DATE NOT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- 2. RBAC (Role-Based Access Control)
-- ==========================================

-- Users (Enhanced with UUID and soft deletes)
CREATE TABLE `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `uuid` CHAR(36) UNIQUE NOT NULL,
  `company_id` INT UNSIGNED NULL,
  `branch_id` INT UNSIGNED NULL,
  `username` VARCHAR(50) UNIQUE NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `email` VARCHAR(100) UNIQUE,
  `full_name` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20),
  `is_active` TINYINT(1) DEFAULT 1,
  `last_login_at` DATETIME,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME NULL,
  INDEX `idx_users_company` (`company_id`),
  INDEX `idx_users_branch` (`branch_id`),
  INDEX `idx_users_active` (`is_active`, `deleted_at`),
  FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Now add the manager_user_id FKs back to branches and warehouses
ALTER TABLE `branches` ADD CONSTRAINT `fk_branches_manager` FOREIGN KEY (`manager_user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE `warehouses` ADD CONSTRAINT `fk_warehouses_manager` FOREIGN KEY (`manager_user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- Roles
CREATE TABLE `roles` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `company_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(50) NOT NULL,
  `slug` VARCHAR(50) NOT NULL,
  `description` TEXT,
  `is_system` TINYINT(1) DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_roles_company_slug` (`company_id`, `slug`),
  FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Permissions
CREATE TABLE `permissions` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `module` VARCHAR(50) NOT NULL,
  `action` VARCHAR(50) NOT NULL,
  `description` VARCHAR(200),
  UNIQUE KEY `uk_permissions_module_action` (`module`, `action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Role Permissions
CREATE TABLE `role_permissions` (
  `role_id` INT UNSIGNED NOT NULL,
  `permission_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`role_id`, `permission_id`),
  FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`permission_id`) REFERENCES `permissions`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User Roles
CREATE TABLE `user_roles` (
  `user_id` INT UNSIGNED NOT NULL,
  `role_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`user_id`, `role_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ==========================================
-- 3. PRODUCT INFORMATION MANAGEMENT (PIM)
-- ==========================================

-- Categories
CREATE TABLE `categories` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `company_id` INT UNSIGNED NOT NULL,
  `parent_id` INT UNSIGNED NULL,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL,
  `level` TINYINT UNSIGNED DEFAULT 0,
  `sort_order` INT UNSIGNED DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_categories_parent` (`parent_id`),
  UNIQUE KEY `uk_categories_company_slug` (`company_id`, `slug`),
  FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`parent_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Units of Measure
CREATE TABLE `units_of_measure` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `company_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(50) NOT NULL,
  `abbreviation` VARCHAR(10) NOT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Products (Enhanced with UUID and soft deletes)
CREATE TABLE `products` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `uuid` CHAR(36) UNIQUE NOT NULL,
  `company_id` INT UNSIGNED NOT NULL,
  `category_id` INT UNSIGNED NOT NULL,
  `sku` VARCHAR(50) UNIQUE NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `description` TEXT,
  `base_uom_id` INT UNSIGNED NOT NULL,
  `hsn_sac_code` VARCHAR(20),
  `tax_slab_id` INT UNSIGNED NULL,
  `barcode` VARCHAR(50),
  `image_path` VARCHAR(255),
  `reorder_level` INT UNSIGNED DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME NULL,
  INDEX `idx_products_company` (`company_id`),
  INDEX `idx_products_category` (`category_id`),
  INDEX `idx_products_sku` (`sku`),
  INDEX `idx_products_barcode` (`barcode`),
  FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`base_uom_id`) REFERENCES `units_of_measure`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`tax_slab_id`) REFERENCES `tax_slabs`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Product UOM Conversions
CREATE TABLE `product_uom_conversions` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT UNSIGNED NOT NULL,
  `from_uom_id` INT UNSIGNED NOT NULL,
  `to_uom_id` INT UNSIGNED NOT NULL,
  `conversion_factor` DECIMAL(10,4) NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_uom_conv` (`product_id`, `from_uom_id`, `to_uom_id`),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`from_uom_id`) REFERENCES `units_of_measure`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`to_uom_id`) REFERENCES `units_of_measure`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Product Prices
CREATE TABLE `product_prices` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT UNSIGNED NOT NULL,
  `price_type` ENUM('primary','secondary','mrp') NOT NULL,
  `price` DECIMAL(12,2) NOT NULL,
  `effective_from` DATE NOT NULL,
  `effective_to` DATE NULL,
  `branch_id` INT UNSIGNED NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_prices_product_type` (`product_id`, `price_type`, `effective_from`),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Schemes
CREATE TABLE `schemes` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `uuid` CHAR(36) UNIQUE NOT NULL,
  `company_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `scheme_type` ENUM('buy_x_get_y','percentage_discount','flat_discount','credit_note') NOT NULL,
  `buy_qty` INT UNSIGNED,
  `free_qty` INT UNSIGNED,
  `discount_percent` DECIMAL(5,2),
  `discount_amount` DECIMAL(12,2),
  `min_order_qty` INT UNSIGNED DEFAULT 0,
  `min_order_value` DECIMAL(12,2) DEFAULT 0.00,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Scheme Products
CREATE TABLE `scheme_products` (
  `scheme_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`scheme_id`, `product_id`),
  FOREIGN KEY (`scheme_id`) REFERENCES `schemes`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ==========================================
-- 4. PROCUREMENT
-- ==========================================

-- Suppliers (Enhanced with UUID and soft deletes)
CREATE TABLE `suppliers` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `uuid` CHAR(36) UNIQUE NOT NULL,
  `company_id` INT UNSIGNED NOT NULL,
  `supplier_code` VARCHAR(20) UNIQUE,
  `name` VARCHAR(150) NOT NULL,
  `contact_person` VARCHAR(100),
  `email` VARCHAR(100),
  `phone` VARCHAR(20),
  `gstin` VARCHAR(15),
  `pan` VARCHAR(10),
  `address` TEXT,
  `city` VARCHAR(100),
  `state` VARCHAR(100),
  `pincode` VARCHAR(10),
  `payment_terms` VARCHAR(100),
  `bank_name` VARCHAR(100),
  `bank_account_no` VARCHAR(30),
  `bank_ifsc` VARCHAR(15),
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME NULL,
  INDEX `idx_suppliers_company` (`company_id`),
  INDEX `idx_suppliers_code` (`supplier_code`),
  FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Purchase Orders (with UUID and soft deletes)
CREATE TABLE `purchase_orders` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `uuid` CHAR(36) UNIQUE NOT NULL,
  `company_id` INT UNSIGNED NOT NULL,
  `branch_id` INT UNSIGNED NOT NULL,
  `warehouse_id` INT UNSIGNED NOT NULL,
  `supplier_id` INT UNSIGNED NOT NULL,
  `po_number` VARCHAR(30) UNIQUE NOT NULL,
  `po_date` DATE NOT NULL,
  `expected_delivery` DATE,
  `status` ENUM('draft','submitted','approved','partially_received','received','cancelled') DEFAULT 'draft',
  `subtotal` DECIMAL(14,2) DEFAULT 0.00,
  `tax_amount` DECIMAL(12,2) DEFAULT 0.00,
  `discount_amount` DECIMAL(12,2) DEFAULT 0.00,
  `total_amount` DECIMAL(14,2) DEFAULT 0.00,
  `notes` TEXT,
  `approved_by` INT UNSIGNED NULL,
  `approved_at` DATETIME,
  `created_by` INT UNSIGNED NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME NULL,
  INDEX `idx_po_supplier` (`supplier_id`),
  INDEX `idx_po_status` (`status`),
  INDEX `idx_po_date` (`po_date`),
  FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`supplier_id`) REFERENCES `suppliers`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`approved_by`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Purchase Order Items
CREATE TABLE `purchase_order_items` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `purchase_order_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `quantity_ordered` DECIMAL(12,3) NOT NULL,
  `quantity_received` DECIMAL(12,3) DEFAULT 0,
  `uom_id` INT UNSIGNED NOT NULL,
  `unit_price` DECIMAL(12,2) NOT NULL,
  `tax_amount` DECIMAL(10,2) DEFAULT 0.00,
  `discount_amount` DECIMAL(10,2) DEFAULT 0.00,
  `line_total` DECIMAL(14,2) NOT NULL,
  INDEX `idx_poi_po` (`purchase_order_id`),
  INDEX `idx_poi_product` (`product_id`),
  FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`uom_id`) REFERENCES `units_of_measure`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Goods Receipt Notes
CREATE TABLE `goods_receipt_notes` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `uuid` CHAR(36) UNIQUE NOT NULL,
  `purchase_order_id` INT UNSIGNED NOT NULL,
  `warehouse_id` INT UNSIGNED NOT NULL,
  `grn_number` VARCHAR(30) UNIQUE NOT NULL,
  `grn_date` DATE NOT NULL,
  `supplier_invoice_no` VARCHAR(50),
  `supplier_invoice_date` DATE,
  `status` ENUM('pending_qc','partially_accepted','accepted','rejected') DEFAULT 'pending_qc',
  `notes` TEXT,
  `received_by` INT UNSIGNED NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_grn_po` (`purchase_order_id`),
  FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`received_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Debit Notes (with UUID and soft deletes)
CREATE TABLE `debit_notes` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `uuid` CHAR(36) UNIQUE NOT NULL,
  `company_id` INT UNSIGNED NOT NULL,
  `supplier_id` INT UNSIGNED NOT NULL,
  `debit_note_number` VARCHAR(30) UNIQUE NOT NULL,
  `debit_note_date` DATE NOT NULL,
  `supplier_return_id` INT UNSIGNED NULL, -- Will add foreign key later
  `amount` DECIMAL(14,2) NOT NULL,
  `tax_amount` DECIMAL(12,2) DEFAULT 0.00,
  `total_amount` DECIMAL(14,2) NOT NULL,
  `status` ENUM('draft','issued','adjusted','cancelled') DEFAULT 'draft',
  `notes` TEXT,
  `created_by` INT UNSIGNED NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` DATETIME NULL,
  FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`supplier_id`) REFERENCES `suppliers`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Supplier Returns
CREATE TABLE `supplier_returns` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `uuid` CHAR(36) UNIQUE NOT NULL,
  `company_id` INT UNSIGNED NOT NULL,
  `supplier_id` INT UNSIGNED NOT NULL,
  `warehouse_id` INT UNSIGNED NOT NULL,
  `return_number` VARCHAR(30) UNIQUE NOT NULL,
  `return_date` DATE NOT NULL,
  `reason` TEXT,
  `status` ENUM('draft','approved','dispatched','completed','cancelled') DEFAULT 'draft',
  `total_amount` DECIMAL(14,2) DEFAULT 0.00,
  `debit_note_id` INT UNSIGNED NULL,
  `created_by` INT UNSIGNED NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`supplier_id`) REFERENCES `suppliers`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`debit_note_id`) REFERENCES `debit_notes`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add FK back to debit_notes for supplier_return_id
ALTER TABLE `debit_notes` ADD CONSTRAINT `fk_dn_supplier_return` FOREIGN KEY (`supplier_return_id`) REFERENCES `supplier_returns`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;


-- ==========================================
-- 5. WAREHOUSE & INVENTORY (WMS)
-- ==========================================

-- Warehouse Zones
CREATE TABLE `warehouse_zones` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `warehouse_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(50) NOT NULL,
  `zone_type` ENUM('storage','receiving','shipping','quarantine','damaged') DEFAULT 'storage',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Warehouse Bins
CREATE TABLE `warehouse_bins` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `warehouse_id` INT UNSIGNED NOT NULL,
  `zone_id` INT UNSIGNED NULL,
  `aisle` VARCHAR(10),
  `rack` VARCHAR(10),
  `shelf` VARCHAR(10),
  `bin_code` VARCHAR(30) NOT NULL,
  `capacity` INT UNSIGNED DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_bin_code_warehouse` (`warehouse_id`, `bin_code`),
  FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`zone_id`) REFERENCES `warehouse_zones`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Product Batches
CREATE TABLE `product_batches` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT UNSIGNED NOT NULL,
  `batch_number` VARCHAR(50) NOT NULL,
  `manufacturing_date` DATE,
  `expiry_date` DATE,
  `barcode` VARCHAR(50),
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_batch_product` (`product_id`),
  INDEX `idx_batch_expiry` (`expiry_date`),
  UNIQUE KEY `uk_batch` (`product_id`, `batch_number`),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- GRN Items (Dependent on Product Batches & Bins)
CREATE TABLE `grn_items` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `grn_id` INT UNSIGNED NOT NULL,
  `po_item_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `batch_id` INT UNSIGNED NULL,
  `quantity_received` DECIMAL(12,3) NOT NULL,
  `quantity_accepted` DECIMAL(12,3) DEFAULT 0,
  `quantity_rejected` DECIMAL(12,3) DEFAULT 0,
  `qc_status` ENUM('pending','accepted','rejected','on_hold') DEFAULT 'pending',
  `qc_remarks` TEXT,
  `bin_id` INT UNSIGNED NULL,
  INDEX `idx_grni_grn` (`grn_id`),
  FOREIGN KEY (`grn_id`) REFERENCES `goods_receipt_notes`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`po_item_id`) REFERENCES `purchase_order_items`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`batch_id`) REFERENCES `product_batches`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  FOREIGN KEY (`bin_id`) REFERENCES `warehouse_bins`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Supplier Return Items
CREATE TABLE `supplier_return_items` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `supplier_return_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `batch_id` INT UNSIGNED NULL,
  `quantity` DECIMAL(12,3) NOT NULL,
  `unit_price` DECIMAL(12,2) NOT NULL,
  `reason` VARCHAR(200),
  FOREIGN KEY (`supplier_return_id`) REFERENCES `supplier_returns`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`batch_id`) REFERENCES `product_batches`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Landing Costs
CREATE TABLE `landing_costs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `grn_id` INT UNSIGNED NOT NULL,
  `cost_type` ENUM('freight','insurance','handling','customs','other') NOT NULL,
  `description` VARCHAR(200),
  `amount` DECIMAL(12,2) NOT NULL,
  `allocation_method` ENUM('by_value','by_quantity','by_weight','manual') DEFAULT 'by_value',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`grn_id`) REFERENCES `goods_receipt_notes`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inventory (No soft deletes - quantities adjusted)
CREATE TABLE `inventory` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT UNSIGNED NOT NULL,
  `warehouse_id` INT UNSIGNED NOT NULL,
  `batch_id` INT UNSIGNED NULL,
  `bin_id` INT UNSIGNED NULL,
  `quantity_on_hand` DECIMAL(12,3) DEFAULT 0,
  `quantity_reserved` DECIMAL(12,3) DEFAULT 0,
  `quantity_available` DECIMAL(12,3) GENERATED ALWAYS AS (quantity_on_hand - quantity_reserved) STORED,
  `last_count_date` DATE,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_inventory` (`product_id`, `warehouse_id`, `batch_id`, `bin_id`),
  INDEX `idx_inventory_warehouse` (`warehouse_id`),
  INDEX `idx_inventory_available` (`quantity_available`),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`batch_id`) REFERENCES `product_batches`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`bin_id`) REFERENCES `warehouse_bins`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inventory Transactions (Immutable log)
CREATE TABLE `inventory_transactions` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT UNSIGNED NOT NULL,
  `warehouse_id` INT UNSIGNED NOT NULL,
  `batch_id` INT UNSIGNED NULL,
  `bin_id` INT UNSIGNED NULL,
  `transaction_type` ENUM('grn_in','sale_out','transfer_in','transfer_out','return_in','return_out','adjustment','van_load','van_return') NOT NULL,
  `reference_type` VARCHAR(50),
  `reference_id` INT UNSIGNED,
  `quantity` DECIMAL(12,3) NOT NULL,
  `running_balance` DECIMAL(12,3),
  `notes` VARCHAR(200),
  `created_by` INT UNSIGNED NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_invt_product_warehouse` (`product_id`, `warehouse_id`),
  INDEX `idx_invt_type` (`transaction_type`),
  INDEX `idx_invt_reference` (`reference_type`, `reference_id`),
  INDEX `idx_invt_created` (`created_at`),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`batch_id`) REFERENCES `product_batches`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  FOREIGN KEY (`bin_id`) REFERENCES `warehouse_bins`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Stock Transfers
CREATE TABLE `stock_transfers` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `uuid` CHAR(36) UNIQUE NOT NULL,
  `company_id` INT UNSIGNED NOT NULL,
  `transfer_number` VARCHAR(30) UNIQUE NOT NULL,
  `from_warehouse_id` INT UNSIGNED NOT NULL,
  `to_warehouse_id` INT UNSIGNED NOT NULL,
  `transfer_date` DATE NOT NULL,
  `status` ENUM('draft','approved','in_transit','received','cancelled') DEFAULT 'draft',
  `notes` TEXT,
  `created_by` INT UNSIGNED NOT NULL,
  `approved_by` INT UNSIGNED NULL,
  `received_by` INT UNSIGNED NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`from_warehouse_id`) REFERENCES `warehouses`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`to_warehouse_id`) REFERENCES `warehouses`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`approved_by`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  FOREIGN KEY (`received_by`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Stock Transfer Items
CREATE TABLE `stock_transfer_items` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `stock_transfer_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `batch_id` INT UNSIGNED NULL,
  `quantity_sent` DECIMAL(12,3) NOT NULL,
  `quantity_received` DECIMAL(12,3) DEFAULT 0,
  FOREIGN KEY (`stock_transfer_id`) REFERENCES `stock_transfers`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`batch_id`) REFERENCES `product_batches`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Stock Adjustments
CREATE TABLE `stock_adjustments` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `uuid` CHAR(36) UNIQUE NOT NULL,
  `warehouse_id` INT UNSIGNED NOT NULL,
  `adjustment_number` VARCHAR(30) UNIQUE NOT NULL,
  `adjustment_date` DATE NOT NULL,
  `adjustment_type` ENUM('cycle_count','damage','expired','other') NOT NULL,
  `status` ENUM('draft','approved','posted') DEFAULT 'draft',
  `notes` TEXT,
  `created_by` INT UNSIGNED NOT NULL,
  `approved_by` INT UNSIGNED NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`approved_by`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Stock Adjustment Items
CREATE TABLE `stock_adjustment_items` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `stock_adjustment_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `batch_id` INT UNSIGNED NULL,
  `bin_id` INT UNSIGNED NULL,
  `system_qty` DECIMAL(12,3) NOT NULL,
  `physical_qty` DECIMAL(12,3) NOT NULL,
  `variance` DECIMAL(12,3) GENERATED ALWAYS AS (physical_qty - system_qty) STORED,
  `reason` VARCHAR(200),
  FOREIGN KEY (`stock_adjustment_id`) REFERENCES `stock_adjustments`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`batch_id`) REFERENCES `product_batches`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  FOREIGN KEY (`bin_id`) REFERENCES `warehouse_bins`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Van Stock
CREATE TABLE `van_stock` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `warehouse_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `batch_id` INT UNSIGNED NULL,
  `quantity_loaded` DECIMAL(12,3) DEFAULT 0,
  `quantity_sold` DECIMAL(12,3) DEFAULT 0,
  `quantity_returned` DECIMAL(12,3) DEFAULT 0,
  `load_date` DATE NOT NULL,
  `status` ENUM('loaded','in_progress','settled') DEFAULT 'loaded',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_van_user_date` (`user_id`, `load_date`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`batch_id`) REFERENCES `product_batches`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ==========================================
-- 6. CRM (DEALER & RETAILER MANAGEMENT)
-- ==========================================

-- Beats (Sales Routes)
CREATE TABLE `beats` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `company_id` INT UNSIGNED NOT NULL,
  `branch_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `code` VARCHAR(20) UNIQUE NOT NULL,
  `area_description` TEXT,
  `assigned_salesman_id` INT UNSIGNED NOT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`assigned_salesman_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Customers (Enhanced with UUID and soft deletes)
CREATE TABLE `customers` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `uuid` CHAR(36) UNIQUE NOT NULL,
  `company_id` INT UNSIGNED NOT NULL,
  `branch_id` INT UNSIGNED NULL,
  `customer_code` VARCHAR(20) UNIQUE,
  `name` VARCHAR(150) NOT NULL,
  `contact_person` VARCHAR(100),
  `email` VARCHAR(100),
  `phone` VARCHAR(20) NOT NULL,
  `alternate_phone` VARCHAR(20),
  `gstin` VARCHAR(15),
  `pan` VARCHAR(10),
  `classification` ENUM('A','B','C','D') DEFAULT 'C',
  `customer_type` ENUM('retailer','dealer','wholesaler','institutional') DEFAULT 'retailer',
  `address` TEXT NOT NULL,
  `city` VARCHAR(100),
  `state` VARCHAR(100),
  `pincode` VARCHAR(10),
  `latitude` DECIMAL(10,8),
  `longitude` DECIMAL(11,8),
  `credit_limit` DECIMAL(12,2) DEFAULT 0.00,
  `credit_days` INT UNSIGNED DEFAULT 0,
  `outstanding_balance` DECIMAL(12,2) DEFAULT 0.00,
  `assigned_salesman_id` INT UNSIGNED NULL,
  `beat_id` INT UNSIGNED NULL,
  `payment_terms` VARCHAR(100),
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME NULL,
  INDEX `idx_customers_company` (`company_id`),
  INDEX `idx_customers_salesman` (`assigned_salesman_id`),
  INDEX `idx_customers_beat` (`beat_id`),
  INDEX `idx_customers_classification` (`classification`),
  INDEX `idx_customers_outstanding` (`outstanding_balance`),
  FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`assigned_salesman_id`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  FOREIGN KEY (`beat_id`) REFERENCES `beats`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Customer Addresses
CREATE TABLE `customer_addresses` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `customer_id` INT UNSIGNED NOT NULL,
  `label` VARCHAR(50) DEFAULT 'Primary',
  `address_line` TEXT NOT NULL,
  `city` VARCHAR(100),
  `state` VARCHAR(100),
  `pincode` VARCHAR(10),
  `latitude` DECIMAL(10,8),
  `longitude` DECIMAL(11,8),
  `is_default` TINYINT(1) DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Beat Retailers
CREATE TABLE `beat_retailers` (
  `beat_id` INT UNSIGNED NOT NULL,
  `customer_id` INT UNSIGNED NOT NULL,
  `visit_order` INT UNSIGNED DEFAULT 0,
  PRIMARY KEY (`beat_id`, `customer_id`),
  FOREIGN KEY (`beat_id`) REFERENCES `beats`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Beat Schedule
CREATE TABLE `beat_schedule` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `beat_id` INT UNSIGNED NOT NULL,
  `day_of_week` ENUM('monday','tuesday','wednesday','thursday','friday','saturday','sunday') NOT NULL,
  `salesman_id` INT UNSIGNED NOT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  UNIQUE KEY `uk_beat_day` (`beat_id`, `day_of_week`),
  FOREIGN KEY (`beat_id`) REFERENCES `beats`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`salesman_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Customer Visits
CREATE TABLE `customer_visits` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `customer_id` INT UNSIGNED NOT NULL,
  `salesman_id` INT UNSIGNED NOT NULL,
  `beat_id` INT UNSIGNED NULL,
  `visit_date` DATETIME NOT NULL,
  `check_in_lat` DECIMAL(10,8),
  `check_in_lng` DECIMAL(11,8),
  `check_out_lat` DECIMAL(10,8),
  `check_out_lng` DECIMAL(11,8),
  `check_in_time` DATETIME,
  `check_out_time` DATETIME,
  `visit_type` ENUM('planned','unplanned') DEFAULT 'planned',
  `outcome` ENUM('order_placed','no_order','complaint','payment_collected','closed') DEFAULT 'no_order',
  `notes` TEXT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_visits_salesman_date` (`salesman_id`, `visit_date`),
  INDEX `idx_visits_customer` (`customer_id`),
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`salesman_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`beat_id`) REFERENCES `beats`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Complaints
CREATE TABLE `complaints` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `uuid` CHAR(36) UNIQUE NOT NULL,
  `company_id` INT UNSIGNED NOT NULL,
  `customer_id` INT UNSIGNED NOT NULL,
  `complaint_number` VARCHAR(30) UNIQUE NOT NULL,
  `complaint_type` ENUM('late_delivery','damaged_product','wrong_product','billing_issue','other') NOT NULL,
  `subject` VARCHAR(200) NOT NULL,
  `description` TEXT,
  `priority` ENUM('low','medium','high','critical') DEFAULT 'medium',
  `status` ENUM('open','in_progress','resolved','closed','escalated') DEFAULT 'open',
  `assigned_to` INT UNSIGNED NULL,
  `sla_due_date` DATETIME,
  `resolved_at` DATETIME,
  `resolution_notes` TEXT,
  `created_by` INT UNSIGNED NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_complaints_customer` (`customer_id`),
  INDEX `idx_complaints_status` (`status`),
  FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`assigned_to`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Complaint History
CREATE TABLE `complaint_history` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `complaint_id` INT UNSIGNED NOT NULL,
  `from_status` VARCHAR(30),
  `to_status` VARCHAR(30),
  `comment` TEXT,
  `changed_by` INT UNSIGNED NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`complaint_id`) REFERENCES `complaints`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`changed_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ==========================================
-- 7. SALES (OUTWARD SUPPLY)
-- ==========================================

-- Quotations
CREATE TABLE `quotations` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `uuid` CHAR(36) UNIQUE NOT NULL,
  `company_id` INT UNSIGNED NOT NULL,
  `branch_id` INT UNSIGNED NOT NULL,
  `customer_id` INT UNSIGNED NOT NULL,
  `quotation_number` VARCHAR(30) UNIQUE NOT NULL,
  `quotation_date` DATE NOT NULL,
  `valid_until` DATE,
  `status` ENUM('draft','sent','accepted','rejected','expired','converted') DEFAULT 'draft',
  `subtotal` DECIMAL(14,2) DEFAULT 0.00,
  `tax_amount` DECIMAL(12,2) DEFAULT 0.00,
  `discount_amount` DECIMAL(12,2) DEFAULT 0.00,
  `total_amount` DECIMAL(14,2) DEFAULT 0.00,
  `notes` TEXT,
  `created_by` INT UNSIGNED NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME NULL,
  FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Quotation Items
CREATE TABLE `quotation_items` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `quotation_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `quantity` DECIMAL(12,3) NOT NULL,
  `uom_id` INT UNSIGNED NOT NULL,
  `unit_price` DECIMAL(12,2) NOT NULL,
  `discount_percent` DECIMAL(5,2) DEFAULT 0.00,
  `discount_amount` DECIMAL(10,2) DEFAULT 0.00,
  `tax_amount` DECIMAL(10,2) DEFAULT 0.00,
  `line_total` DECIMAL(14,2) NOT NULL,
  FOREIGN KEY (`quotation_id`) REFERENCES `quotations`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`uom_id`) REFERENCES `units_of_measure`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sales Orders
CREATE TABLE `sales_orders` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `uuid` CHAR(36) UNIQUE NOT NULL,
  `company_id` INT UNSIGNED NOT NULL,
  `branch_id` INT UNSIGNED NOT NULL,
  `warehouse_id` INT UNSIGNED NOT NULL,
  `customer_id` INT UNSIGNED NOT NULL,
  `quotation_id` INT UNSIGNED NULL,
  `order_number` VARCHAR(30) UNIQUE NOT NULL,
  `order_date` DATETIME NOT NULL,
  `expected_delivery` DATE,
  `status` ENUM('draft','confirmed','processing','partially_fulfilled','fulfilled','invoiced','cancelled') DEFAULT 'draft',
  `payment_status` ENUM('unpaid','partially_paid','paid') DEFAULT 'unpaid',
  `subtotal` DECIMAL(14,2) DEFAULT 0.00,
  `tax_amount` DECIMAL(12,2) DEFAULT 0.00,
  `discount_amount` DECIMAL(12,2) DEFAULT 0.00,
  `scheme_discount` DECIMAL(12,2) DEFAULT 0.00,
  `total_amount` DECIMAL(14,2) DEFAULT 0.00,
  `amount_paid` DECIMAL(14,2) DEFAULT 0.00,
  `amount_due` DECIMAL(14,2) GENERATED ALWAYS AS (total_amount - amount_paid) STORED,
  `notes` TEXT,
  `salesman_id` INT UNSIGNED NOT NULL,
  `approved_by` INT UNSIGNED NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME NULL,
  INDEX `idx_so_customer` (`customer_id`),
  INDEX `idx_so_salesman` (`salesman_id`),
  INDEX `idx_so_status` (`status`),
  INDEX `idx_so_payment` (`payment_status`),
  INDEX `idx_so_date` (`order_date`),
  FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`quotation_id`) REFERENCES `quotations`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  FOREIGN KEY (`salesman_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`approved_by`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sales Order Items
CREATE TABLE `sales_order_items` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `sales_order_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `batch_id` INT UNSIGNED NULL,
  `quantity` DECIMAL(12,3) NOT NULL,
  `quantity_fulfilled` DECIMAL(12,3) DEFAULT 0,
  `uom_id` INT UNSIGNED NOT NULL,
  `unit_price` DECIMAL(12,2) NOT NULL,
  `discount_percent` DECIMAL(5,2) DEFAULT 0.00,
  `discount_amount` DECIMAL(10,2) DEFAULT 0.00,
  `tax_slab_id` INT UNSIGNED NULL,
  `cgst_amount` DECIMAL(10,2) DEFAULT 0.00,
  `sgst_amount` DECIMAL(10,2) DEFAULT 0.00,
  `igst_amount` DECIMAL(10,2) DEFAULT 0.00,
  `line_total` DECIMAL(14,2) NOT NULL,
  INDEX `idx_soi_so` (`sales_order_id`),
  INDEX `idx_soi_product` (`product_id`),
  FOREIGN KEY (`sales_order_id`) REFERENCES `sales_orders`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`batch_id`) REFERENCES `product_batches`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  FOREIGN KEY (`uom_id`) REFERENCES `units_of_measure`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`tax_slab_id`) REFERENCES `tax_slabs`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sales Invoices
CREATE TABLE `sales_invoices` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `uuid` CHAR(36) UNIQUE NOT NULL,
  `company_id` INT UNSIGNED NOT NULL,
  `branch_id` INT UNSIGNED NOT NULL,
  `customer_id` INT UNSIGNED NOT NULL,
  `sales_order_id` INT UNSIGNED NULL,
  `invoice_number` VARCHAR(30) UNIQUE NOT NULL,
  `invoice_date` DATE NOT NULL,
  `due_date` DATE,
  `financial_year_id` INT UNSIGNED NOT NULL,
  `subtotal` DECIMAL(14,2) DEFAULT 0.00,
  `cgst_total` DECIMAL(12,2) DEFAULT 0.00,
  `sgst_total` DECIMAL(12,2) DEFAULT 0.00,
  `igst_total` DECIMAL(12,2) DEFAULT 0.00,
  `cess_total` DECIMAL(12,2) DEFAULT 0.00,
  `discount_total` DECIMAL(12,2) DEFAULT 0.00,
  `round_off` DECIMAL(5,2) DEFAULT 0.00,
  `grand_total` DECIMAL(14,2) NOT NULL,
  `amount_paid` DECIMAL(14,2) DEFAULT 0.00,
  `status` ENUM('draft','issued','partially_paid','paid','overdue','cancelled','credit_noted') DEFAULT 'draft',
  `notes` TEXT,
  `created_by` INT UNSIGNED NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME NULL,
  INDEX `idx_si_customer` (`customer_id`),
  INDEX `idx_si_date` (`invoice_date`),
  INDEX `idx_si_status` (`status`),
  INDEX `idx_si_fy` (`financial_year_id`),
  FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`sales_order_id`) REFERENCES `sales_orders`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  FOREIGN KEY (`financial_year_id`) REFERENCES `financial_years`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sales Invoice Items
CREATE TABLE `sales_invoice_items` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `sales_invoice_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `batch_id` INT UNSIGNED NULL,
  `quantity` DECIMAL(12,3) NOT NULL,
  `uom_id` INT UNSIGNED NOT NULL,
  `unit_price` DECIMAL(12,2) NOT NULL,
  `mrp` DECIMAL(12,2),
  `discount_percent` DECIMAL(5,2) DEFAULT 0.00,
  `discount_amount` DECIMAL(10,2) DEFAULT 0.00,
  `hsn_sac_code` VARCHAR(20),
  `tax_slab_id` INT UNSIGNED NULL,
  `cgst_amount` DECIMAL(10,2) DEFAULT 0.00,
  `sgst_amount` DECIMAL(10,2) DEFAULT 0.00,
  `igst_amount` DECIMAL(10,2) DEFAULT 0.00,
  `line_total` DECIMAL(14,2) NOT NULL,
  FOREIGN KEY (`sales_invoice_id`) REFERENCES `sales_invoices`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`batch_id`) REFERENCES `product_batches`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  FOREIGN KEY (`uom_id`) REFERENCES `units_of_measure`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`tax_slab_id`) REFERENCES `tax_slabs`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Credit Notes (with UUID and soft deletes)
CREATE TABLE `credit_notes` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `uuid` CHAR(36) UNIQUE NOT NULL,
  `company_id` INT UNSIGNED NOT NULL,
  `customer_id` INT UNSIGNED NOT NULL,
  `credit_note_number` VARCHAR(30) UNIQUE NOT NULL,
  `credit_note_date` DATE NOT NULL,
  `sales_return_id` INT UNSIGNED NULL, -- Will add foreign key constraint later
  `amount` DECIMAL(14,2) NOT NULL,
  `tax_amount` DECIMAL(12,2) DEFAULT 0.00,
  `total_amount` DECIMAL(14,2) NOT NULL,
  `status` ENUM('draft','issued','partially_adjusted','adjusted','cancelled') DEFAULT 'draft',
  `balance_remaining` DECIMAL(14,2) NOT NULL,
  `notes` TEXT,
  `created_by` INT UNSIGNED NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` DATETIME NULL,
  FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sales Returns
CREATE TABLE `sales_returns` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `uuid` CHAR(36) UNIQUE NOT NULL,
  `company_id` INT UNSIGNED NOT NULL,
  `customer_id` INT UNSIGNED NOT NULL,
  `sales_invoice_id` INT UNSIGNED NULL,
  `return_number` VARCHAR(30) UNIQUE NOT NULL,
  `return_date` DATE NOT NULL,
  `reason` TEXT,
  `status` ENUM('draft','approved','received','credit_noted','cancelled') DEFAULT 'draft',
  `total_amount` DECIMAL(14,2) DEFAULT 0.00,
  `credit_note_id` INT UNSIGNED NULL,
  `warehouse_id` INT UNSIGNED NOT NULL,
  `created_by` INT UNSIGNED NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`sales_invoice_id`) REFERENCES `sales_invoices`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  FOREIGN KEY (`credit_note_id`) REFERENCES `credit_notes`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add FK back to credit_notes for sales_return_id
ALTER TABLE `credit_notes` ADD CONSTRAINT `fk_cn_sales_return` FOREIGN KEY (`sales_return_id`) REFERENCES `sales_returns`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- Sales Return Items
CREATE TABLE `sales_return_items` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `sales_return_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `batch_id` INT UNSIGNED NULL,
  `quantity` DECIMAL(12,3) NOT NULL,
  `unit_price` DECIMAL(12,2) NOT NULL,
  `return_condition` ENUM('saleable','damaged','expired') DEFAULT 'saleable',
  `reason` VARCHAR(200),
  FOREIGN KEY (`sales_return_id`) REFERENCES `sales_returns`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`batch_id`) REFERENCES `product_batches`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Indents (Retailer Demand)
CREATE TABLE `indents` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `uuid` CHAR(36) UNIQUE NOT NULL,
  `company_id` INT UNSIGNED NOT NULL,
  `customer_id` INT UNSIGNED NOT NULL,
  `indent_number` VARCHAR(30) UNIQUE NOT NULL,
  `indent_date` DATETIME NOT NULL,
  `status` ENUM('submitted','confirmed','converted_to_order','rejected') DEFAULT 'submitted',
  `total_amount` DECIMAL(14,2) DEFAULT 0.00,
  `notes` TEXT,
  `converted_order_id` INT UNSIGNED NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`converted_order_id`) REFERENCES `sales_orders`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Indent Items
CREATE TABLE `indent_items` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `indent_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `quantity` DECIMAL(12,3) NOT NULL,
  `uom_id` INT UNSIGNED NOT NULL,
  `notes` VARCHAR(200),
  FOREIGN KEY (`indent_id`) REFERENCES `indents`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`uom_id`) REFERENCES `units_of_measure`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ==========================================
-- 8. LOGISTICS & DELIVERY
-- ==========================================

-- Vehicles
CREATE TABLE `vehicles` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `company_id` INT UNSIGNED NOT NULL,
  `branch_id` INT UNSIGNED NOT NULL,
  `vehicle_number` VARCHAR(20) UNIQUE NOT NULL,
  `vehicle_type` ENUM('van','truck','bike','tempo','other') DEFAULT 'van',
  `make_model` VARCHAR(100),
  `capacity_kg` DECIMAL(10,2),
  `capacity_volume` DECIMAL(10,2),
  `fuel_type` ENUM('petrol','diesel','electric','cng') DEFAULT 'diesel',
  `insurance_expiry` DATE,
  `fitness_expiry` DATE,
  `assigned_driver_id` INT UNSIGNED NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`assigned_driver_id`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Vehicle Expenses
CREATE TABLE `vehicle_expenses` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `vehicle_id` INT UNSIGNED NOT NULL,
  `expense_type` ENUM('fuel','maintenance','insurance','toll','challan','other') NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `expense_date` DATE NOT NULL,
  `odometer_reading` INT UNSIGNED,
  `description` VARCHAR(200),
  `receipt_path` VARCHAR(255),
  `created_by` INT UNSIGNED NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Delivery Plans
CREATE TABLE `delivery_plans` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `uuid` CHAR(36) UNIQUE NOT NULL,
  `company_id` INT UNSIGNED NOT NULL,
  `branch_id` INT UNSIGNED NOT NULL,
  `plan_number` VARCHAR(30) UNIQUE NOT NULL,
  `plan_date` DATE NOT NULL,
  `vehicle_id` INT UNSIGNED NOT NULL,
  `driver_id` INT UNSIGNED NOT NULL,
  `status` ENUM('planned','in_progress','completed','cancelled') DEFAULT 'planned',
  `total_invoices` INT UNSIGNED DEFAULT 0,
  `total_value` DECIMAL(14,2) DEFAULT 0.00,
  `notes` TEXT,
  `created_by` INT UNSIGNED NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`driver_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Delivery Plan Items
CREATE TABLE `delivery_plan_items` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `delivery_plan_id` INT UNSIGNED NOT NULL,
  `sales_invoice_id` INT UNSIGNED NOT NULL,
  `delivery_sequence` INT UNSIGNED DEFAULT 0,
  `status` ENUM('pending','delivered','partial','failed','rescheduled') DEFAULT 'pending',
  `delivery_time` DATETIME,
  `failure_reason` VARCHAR(200),
  FOREIGN KEY (`delivery_plan_id`) REFERENCES `delivery_plans`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`sales_invoice_id`) REFERENCES `sales_invoices`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Proof of Delivery
CREATE TABLE `proof_of_delivery` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `delivery_plan_item_id` INT UNSIGNED NOT NULL,
  `signature_path` VARCHAR(255),
  `photo_path` VARCHAR(255),
  `otp_code` VARCHAR(10),
  `otp_verified` TINYINT(1) DEFAULT 0,
  `receiver_name` VARCHAR(100),
  `latitude` DECIMAL(10,8),
  `longitude` DECIMAL(11,8),
  `delivered_at` DATETIME NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`delivery_plan_item_id`) REFERENCES `delivery_plan_items`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ==========================================
-- 9. ACCOUNTING & FINANCE
-- ==========================================

-- Chart of Accounts
CREATE TABLE `chart_of_accounts` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `company_id` INT UNSIGNED NOT NULL,
  `parent_id` INT UNSIGNED NULL,
  `account_code` VARCHAR(20) NOT NULL,
  `account_name` VARCHAR(100) NOT NULL,
  `account_type` ENUM('asset','liability','equity','revenue','expense') NOT NULL,
  `account_subtype` VARCHAR(50),
  `is_system` TINYINT(1) DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `balance` DECIMAL(14,2) DEFAULT 0.00,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_coa_company_code` (`company_id`, `account_code`),
  INDEX `idx_coa_type` (`account_type`),
  FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`parent_id`) REFERENCES `chart_of_accounts`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Journal Entries
CREATE TABLE `journal_entries` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `company_id` INT UNSIGNED NOT NULL,
  `financial_year_id` INT UNSIGNED NOT NULL,
  `entry_number` VARCHAR(30) UNIQUE NOT NULL,
  `entry_date` DATE NOT NULL,
  `reference_type` VARCHAR(50),
  `reference_id` INT UNSIGNED,
  `narration` TEXT,
  `status` ENUM('draft','posted','reversed') DEFAULT 'draft',
  `is_auto_generated` TINYINT(1) DEFAULT 0,
  `created_by` INT UNSIGNED NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_je_date` (`entry_date`),
  INDEX `idx_je_reference` (`reference_type`, `reference_id`),
  FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`financial_year_id`) REFERENCES `financial_years`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Journal Entry Lines
CREATE TABLE `journal_entry_lines` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `journal_entry_id` BIGINT UNSIGNED NOT NULL,
  `account_id` INT UNSIGNED NOT NULL,
  `debit_amount` DECIMAL(14,2) DEFAULT 0.00,
  `credit_amount` DECIMAL(14,2) DEFAULT 0.00,
  `description` VARCHAR(200),
  INDEX `idx_jel_entry` (`journal_entry_id`),
  INDEX `idx_jel_account` (`account_id`),
  FOREIGN KEY (`journal_entry_id`) REFERENCES `journal_entries`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`account_id`) REFERENCES `chart_of_accounts`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payments Received
CREATE TABLE `payments_received` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `uuid` CHAR(36) UNIQUE NOT NULL,
  `company_id` INT UNSIGNED NOT NULL,
  `customer_id` INT UNSIGNED NOT NULL,
  `receipt_number` VARCHAR(30) UNIQUE NOT NULL,
  `receipt_date` DATE NOT NULL,
  `amount` DECIMAL(14,2) NOT NULL,
  `payment_mode` ENUM('cash','cheque','upi','neft','rtgs','credit_note','other') NOT NULL,
  `cheque_number` VARCHAR(20),
  `cheque_date` DATE,
  `bank_name` VARCHAR(100),
  `transaction_ref` VARCHAR(50),
  `status` ENUM('received','deposited','cleared','bounced','cancelled') DEFAULT 'received',
  `notes` TEXT,
  `collected_by` INT UNSIGNED NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_pr_customer` (`customer_id`),
  INDEX `idx_pr_date` (`receipt_date`),
  FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`collected_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payment Allocations
CREATE TABLE `payment_allocations` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `payment_received_id` INT UNSIGNED NOT NULL,
  `sales_invoice_id` INT UNSIGNED NOT NULL,
  `amount_allocated` DECIMAL(14,2) NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`payment_received_id`) REFERENCES `payments_received`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`sales_invoice_id`) REFERENCES `sales_invoices`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bank Accounts
CREATE TABLE `bank_accounts` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `company_id` INT UNSIGNED NOT NULL,
  `account_name` VARCHAR(100) NOT NULL,
  `bank_name` VARCHAR(100) NOT NULL,
  `account_number` VARCHAR(30) NOT NULL,
  `ifsc_code` VARCHAR(15),
  `branch_name` VARCHAR(100),
  `opening_balance` DECIMAL(14,2) DEFAULT 0.00,
  `current_balance` DECIMAL(14,2) DEFAULT 0.00,
  `coa_account_id` INT UNSIGNED NOT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`coa_account_id`) REFERENCES `chart_of_accounts`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payments Made
CREATE TABLE `payments_made` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `uuid` CHAR(36) UNIQUE NOT NULL,
  `company_id` INT UNSIGNED NOT NULL,
  `supplier_id` INT UNSIGNED NOT NULL,
  `payment_number` VARCHAR(30) UNIQUE NOT NULL,
  `payment_date` DATE NOT NULL,
  `amount` DECIMAL(14,2) NOT NULL,
  `payment_mode` ENUM('cheque','neft','rtgs','upi','cash','debit_note','other') NOT NULL,
  `cheque_number` VARCHAR(20),
  `bank_account_id` INT UNSIGNED NULL,
  `transaction_ref` VARCHAR(50),
  `status` ENUM('draft','approved','paid','cleared','cancelled') DEFAULT 'draft',
  `tds_amount` DECIMAL(10,2) DEFAULT 0.00,
  `notes` TEXT,
  `created_by` INT UNSIGNED NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_pm_supplier` (`supplier_id`),
  FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`supplier_id`) REFERENCES `suppliers`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`bank_account_id`) REFERENCES `bank_accounts`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bank Transactions
CREATE TABLE `bank_transactions` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `bank_account_id` INT UNSIGNED NOT NULL,
  `transaction_date` DATE NOT NULL,
  `description` VARCHAR(200),
  `debit_amount` DECIMAL(14,2) DEFAULT 0.00,
  `credit_amount` DECIMAL(14,2) DEFAULT 0.00,
  `balance` DECIMAL(14,2),
  `reference` VARCHAR(50),
  `is_reconciled` TINYINT(1) DEFAULT 0,
  `reconciled_with_type` VARCHAR(50),
  `reconciled_with_id` INT UNSIGNED,
  `imported_at` DATETIME,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_bt_bank` (`bank_account_id`),
  INDEX `idx_bt_reconciled` (`is_reconciled`),
  FOREIGN KEY (`bank_account_id`) REFERENCES `bank_accounts`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Expense Categories
CREATE TABLE `expense_categories` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `company_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `coa_account_id` INT UNSIGNED NOT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`coa_account_id`) REFERENCES `chart_of_accounts`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Expenses
CREATE TABLE `expenses` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `uuid` CHAR(36) UNIQUE NOT NULL,
  `company_id` INT UNSIGNED NOT NULL,
  `branch_id` INT UNSIGNED NOT NULL,
  `expense_number` VARCHAR(30) UNIQUE NOT NULL,
  `expense_date` DATE NOT NULL,
  `category_id` INT UNSIGNED NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `tax_amount` DECIMAL(10,2) DEFAULT 0.00,
  `total_amount` DECIMAL(12,2) NOT NULL,
  `payment_mode` ENUM('cash','petty_cash','bank','card') DEFAULT 'cash',
  `description` TEXT,
  `receipt_path` VARCHAR(255),
  `status` ENUM('draft','submitted','approved','paid','rejected') DEFAULT 'draft',
  `approved_by` INT UNSIGNED NULL,
  `created_by` INT UNSIGNED NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME NULL,
  FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`category_id`) REFERENCES `expense_categories`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`approved_by`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ==========================================
-- 10. AUTOMATION & ALERTS
-- ==========================================

-- Alert Rules
CREATE TABLE `alert_rules` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `company_id` INT UNSIGNED NOT NULL,
  `rule_name` VARCHAR(100) NOT NULL,
  `rule_type` ENUM('low_stock','overdue_payment','expiry_warning','po_auto_generate','custom') NOT NULL,
  `conditions_json` JSON,
  `action_type` ENUM('notification','email','sms','auto_po','webhook') NOT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notifications
CREATE TABLE `notifications` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `message` TEXT,
  `notification_type` VARCHAR(50),
  `reference_type` VARCHAR(50),
  `reference_id` INT UNSIGNED,
  `is_read` TINYINT(1) DEFAULT 0,
  `read_at` DATETIME,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_notif_user_read` (`user_id`, `is_read`),
  INDEX `idx_notif_created` (`created_at`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Approval Workflows
CREATE TABLE `approval_workflows` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `company_id` INT UNSIGNED NOT NULL,
  `workflow_name` VARCHAR(100) NOT NULL,
  `entity_type` VARCHAR(50) NOT NULL,
  `trigger_condition` JSON,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Approval Workflow Steps
CREATE TABLE `approval_workflow_steps` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `workflow_id` INT UNSIGNED NOT NULL,
  `step_order` INT UNSIGNED NOT NULL,
  `approver_role_id` INT UNSIGNED NOT NULL,
  `approver_user_id` INT UNSIGNED NULL,
  `is_mandatory` TINYINT(1) DEFAULT 1,
  FOREIGN KEY (`workflow_id`) REFERENCES `approval_workflows`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`approver_role_id`) REFERENCES `roles`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`approver_user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Approval Requests
CREATE TABLE `approval_requests` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `uuid` CHAR(36) UNIQUE NOT NULL,
  `workflow_id` INT UNSIGNED NOT NULL,
  `entity_type` VARCHAR(50) NOT NULL,
  `entity_id` INT UNSIGNED NOT NULL,
  `current_step` INT UNSIGNED DEFAULT 1,
  `status` ENUM('pending','approved','rejected','escalated') DEFAULT 'pending',
  `requested_by` INT UNSIGNED NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_ar_entity` (`entity_type`, `entity_id`),
  INDEX `idx_ar_status` (`status`),
  FOREIGN KEY (`workflow_id`) REFERENCES `approval_workflows`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`requested_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Approval History
CREATE TABLE `approval_history` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `approval_request_id` INT UNSIGNED NOT NULL,
  `step_order` INT UNSIGNED NOT NULL,
  `action` ENUM('approved','rejected','escalated','commented') NOT NULL,
  `comment` TEXT,
  `actioned_by` INT UNSIGNED NOT NULL,
  `actioned_at` DATETIME NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`approval_request_id`) REFERENCES `approval_requests`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`actioned_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Email Queue
CREATE TABLE `email_queue` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `to_email` VARCHAR(200) NOT NULL,
  `cc_email` VARCHAR(500),
  `subject` VARCHAR(200) NOT NULL,
  `body_html` TEXT NOT NULL,
  `status` ENUM('queued','sending','sent','failed') DEFAULT 'queued',
  `attempts` TINYINT UNSIGNED DEFAULT 0,
  `sent_at` DATETIME,
  `error_message` TEXT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_eq_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SMS Queue
CREATE TABLE `sms_queue` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `to_phone` VARCHAR(20) NOT NULL,
  `message` VARCHAR(500) NOT NULL,
  `status` ENUM('queued','sent','failed') DEFAULT 'queued',
  `attempts` TINYINT UNSIGNED DEFAULT 0,
  `sent_at` DATETIME,
  `error_message` TEXT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_sq_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ==========================================
-- 11. BI, AUDIT & CONFIGURATION
-- ==========================================

-- Salesman Targets
CREATE TABLE `salesman_targets` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `company_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `target_month` DATE NOT NULL,
  `target_value` DECIMAL(14,2) NOT NULL,
  `target_qty` INT UNSIGNED DEFAULT 0,
  `achieved_value` DECIMAL(14,2) DEFAULT 0.00,
  `achieved_qty` INT UNSIGNED DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_target` (`user_id`, `target_month`),
  FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Audit Log
CREATE TABLE `audit_log` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NULL,
  `action` VARCHAR(50) NOT NULL,
  `entity_type` VARCHAR(50) NOT NULL,
  `entity_id` INT UNSIGNED,
  `old_values` JSON,
  `new_values` JSON,
  `ip_address` VARCHAR(45),
  `user_agent` VARCHAR(255),
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_audit_user` (`user_id`),
  INDEX `idx_audit_entity` (`entity_type`, `entity_id`),
  INDEX `idx_audit_created` (`created_at`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Settings
CREATE TABLE `settings` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `company_id` INT UNSIGNED NOT NULL,
  `setting_key` VARCHAR(100) NOT NULL,
  `setting_value` TEXT,
  `setting_type` ENUM('string','integer','boolean','json') DEFAULT 'string',
  UNIQUE KEY `uk_settings` (`company_id`, `setting_key`),
  FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Number Sequences
CREATE TABLE `number_sequences` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `company_id` INT UNSIGNED NOT NULL,
  `entity_type` VARCHAR(50) NOT NULL,
  `prefix` VARCHAR(20) NOT NULL,
  `financial_year_id` INT UNSIGNED NOT NULL,
  `current_number` INT UNSIGNED DEFAULT 0,
  `padding` TINYINT UNSIGNED DEFAULT 5,
  UNIQUE KEY `uk_seq` (`company_id`, `entity_type`, `financial_year_id`),
  FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`financial_year_id`) REFERENCES `financial_years`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS=1;
