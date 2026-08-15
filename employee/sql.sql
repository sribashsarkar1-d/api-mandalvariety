

-- =========================================
-- 1. ADMIN / EMPLOYEE USERS
-- =========================================
CREATE TABLE IF NOT EXISTS employee_users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'employee') NOT NULL DEFAULT 'employee',
    phone VARCHAR(20) NULL,
    profile_image VARCHAR(255) NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    last_login DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;


-- =========================================
-- 2. CATEGORIES
-- =========================================
CREATE TABLE IF NOT EXISTS employee_categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    image VARCHAR(255) NULL,
    description TEXT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;


-- =========================================
-- 3. PRODUCTS
-- =========================================
CREATE TABLE IF NOT EXISTS employee_products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id INT UNSIGNED NULL,

    name VARCHAR(200) NOT NULL,
    sku VARCHAR(100) NULL UNIQUE,
    barcode VARCHAR(100) NULL UNIQUE,

    image VARCHAR(255) NULL,
    brand VARCHAR(100) NULL,

    unit ENUM(
        'piece',
        'kg',
        'gram',
        'liter',
        'ml',
        'packet',
        'box',
        'dozen'
    ) NOT NULL DEFAULT 'piece',

    purchase_price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    selling_price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    mrp DECIMAL(12,2) NOT NULL DEFAULT 0.00,

    gst_percent DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    discount DECIMAL(12,2) NOT NULL DEFAULT 0.00,

    expiry_date DATE NULL,

    minimum_stock DECIMAL(12,3) NOT NULL DEFAULT 0,

    description TEXT NULL,

    status ENUM('active', 'inactive', 'expired') NOT NULL DEFAULT 'active',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_product_category
        FOREIGN KEY (category_id)
        REFERENCES employee_categories(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB;


-- =========================================
-- 4. PRODUCT STOCK
-- =========================================
CREATE TABLE IF NOT EXISTS employee_product_stock (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    product_id INT UNSIGNED NOT NULL,

    quantity DECIMAL(12,3) NOT NULL DEFAULT 0,
    reserved_quantity DECIMAL(12,3) NOT NULL DEFAULT 0,

    stock_status ENUM(
        'in_stock',
        'low_stock',
        'out_of_stock'
    ) NOT NULL DEFAULT 'in_stock',

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_stock_product
        FOREIGN KEY (product_id)
        REFERENCES employee_products(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    UNIQUE KEY unique_product_stock (product_id)
) ENGINE=InnoDB;


-- =========================================
-- 5. CUSTOMERS
-- =========================================
CREATE TABLE IF NOT EXISTS employee_customers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(150) NOT NULL,
    phone VARCHAR(20) NULL,
    email VARCHAR(150) NULL,
    address TEXT NULL,

    total_purchase DECIMAL(14,2) NOT NULL DEFAULT 0,
    total_bills INT UNSIGNED NOT NULL DEFAULT 0,

    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;


-- =========================================
-- 6. SALES / BILL
-- =========================================
CREATE TABLE IF NOT EXISTS employee_sales (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    invoice_number VARCHAR(50) NOT NULL UNIQUE,

    customer_id INT UNSIGNED NULL,
    employee_id INT UNSIGNED NOT NULL,

    subtotal DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    discount DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    gst_amount DECIMAL(14,2) NOT NULL DEFAULT 0.00,

    grand_total DECIMAL(14,2) NOT NULL DEFAULT 0.00,

    payment_method ENUM(
        'cash',
        'upi',
        'card',
        'mixed',
        'credit'
    ) NOT NULL DEFAULT 'cash',

    payment_status ENUM(
        'paid',
        'partial',
        'pending'
    ) NOT NULL DEFAULT 'paid',

    notes TEXT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_sale_customer
        FOREIGN KEY (customer_id)
        REFERENCES employee_customers(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    CONSTRAINT fk_sale_employee
        FOREIGN KEY (employee_id)
        REFERENCES employee_users(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB;


-- =========================================
-- 7. SALE ITEMS
-- =========================================
CREATE TABLE IF NOT EXISTS employee_sale_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    sale_id BIGINT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,

    product_name VARCHAR(200) NOT NULL,

    quantity DECIMAL(12,3) NOT NULL DEFAULT 1,

    unit VARCHAR(30) NOT NULL,

    unit_price DECIMAL(12,2) NOT NULL DEFAULT 0.00,

    discount DECIMAL(12,2) NOT NULL DEFAULT 0.00,

    gst_percent DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    gst_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,

    total_price DECIMAL(14,2) NOT NULL DEFAULT 0.00,

    CONSTRAINT fk_sale_item_sale
        FOREIGN KEY (sale_id)
        REFERENCES employee_sales(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_sale_item_product
        FOREIGN KEY (product_id)
        REFERENCES employee_products(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB;


-- =========================================
-- 8. PAYMENTS
-- =========================================
CREATE TABLE IF NOT EXISTS employee_payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    sale_id BIGINT UNSIGNED NOT NULL,

    amount DECIMAL(14,2) NOT NULL DEFAULT 0.00,

    payment_method ENUM(
        'cash',
        'upi',
        'card',
        'mixed',
        'credit'
    ) NOT NULL DEFAULT 'cash',

    transaction_id VARCHAR(150) NULL,

    payment_status ENUM(
        'success',
        'pending',
        'failed'
    ) NOT NULL DEFAULT 'success',

    paid_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_payment_sale
        FOREIGN KEY (sale_id)
        REFERENCES employee_sales(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB;


-- =========================================
-- 9. SUPPLIERS
-- =========================================
CREATE TABLE IF NOT EXISTS employee_suppliers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(150) NOT NULL,
    company_name VARCHAR(200) NULL,

    phone VARCHAR(20) NULL,
    email VARCHAR(150) NULL,

    address TEXT NULL,

    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;


-- =========================================
-- 10. PURCHASES
-- =========================================
CREATE TABLE IF NOT EXISTS employee_purchases (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    purchase_number VARCHAR(50) NOT NULL UNIQUE,

    supplier_id INT UNSIGNED NULL,
    employee_id INT UNSIGNED NOT NULL,

    subtotal DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    discount DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    gst_amount DECIMAL(14,2) NOT NULL DEFAULT 0.00,

    grand_total DECIMAL(14,2) NOT NULL DEFAULT 0.00,

    payment_status ENUM(
        'paid',
        'partial',
        'pending'
    ) NOT NULL DEFAULT 'paid',

    notes TEXT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_purchase_supplier
        FOREIGN KEY (supplier_id)
        REFERENCES employee_suppliers(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    CONSTRAINT fk_purchase_employee
        FOREIGN KEY (employee_id)
        REFERENCES employee_users(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB;


-- =========================================
-- 11. PURCHASE ITEMS
-- =========================================
CREATE TABLE IF NOT EXISTS employee_purchase_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    purchase_id BIGINT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,

    quantity DECIMAL(12,3) NOT NULL DEFAULT 1,

    unit VARCHAR(30) NOT NULL,

    purchase_price DECIMAL(12,2) NOT NULL DEFAULT 0.00,

    total_price DECIMAL(14,2) NOT NULL DEFAULT 0.00,

    expiry_date DATE NULL,

    CONSTRAINT fk_purchase_item_purchase
        FOREIGN KEY (purchase_id)
        REFERENCES employee_purchases(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_purchase_item_product
        FOREIGN KEY (product_id)
        REFERENCES employee_products(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB;


-- =========================================
-- 12. EXPENSES
-- =========================================
CREATE TABLE IF NOT EXISTS employee_expenses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    title VARCHAR(200) NOT NULL,

    category VARCHAR(100) NULL,

    amount DECIMAL(14,2) NOT NULL DEFAULT 0.00,

    description TEXT NULL,

    expense_date DATE NOT NULL,

    created_by INT UNSIGNED NOT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_expense_employee
        FOREIGN KEY (created_by)
        REFERENCES employee_users(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB;


-- =========================================
-- 13. STOCK MOVEMENTS
-- =========================================
CREATE TABLE IF NOT EXISTS employee_stock_movements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    product_id INT UNSIGNED NOT NULL,

    employee_id INT UNSIGNED NOT NULL,

    movement_type ENUM(
        'purchase',
        'sale',
        'return',
        'adjustment',
        'damage',
        'expired'
    ) NOT NULL,

    quantity DECIMAL(12,3) NOT NULL,

    reference_id BIGINT UNSIGNED NULL,

    note TEXT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_movement_product
        FOREIGN KEY (product_id)
        REFERENCES employee_products(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT fk_movement_employee
        FOREIGN KEY (employee_id)
        REFERENCES employee_users(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB;


-- =========================================
-- INDEXES
-- =========================================

CREATE INDEX idx_products_name
ON employee_products(name);

CREATE INDEX idx_products_category
ON employee_products(category_id);

CREATE INDEX idx_products_expiry
ON employee_products(expiry_date);

CREATE INDEX idx_sales_date
ON employee_sales(created_at);

CREATE INDEX idx_sales_employee
ON employee_sales(employee_id);

CREATE INDEX idx_sale_items_sale
ON employee_sale_items(sale_id);

CREATE INDEX idx_stock_movement_product
ON employee_stock_movements(product_id);