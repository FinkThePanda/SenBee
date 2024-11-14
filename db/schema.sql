-- db/schema.sql

-- Drop existing tables
DROP TABLE IF EXISTS sync_history;
DROP TABLE IF EXISTS companies;

-- Create companies table with timestamps
CREATE TABLE companies (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    cvr_number VARCHAR(8) UNIQUE NOT NULL,
    name VARCHAR(255),
    phone VARCHAR(50),
    email VARCHAR(255),
    address TEXT,
    last_synced DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Create index for faster CVR lookups
CREATE INDEX idx_cvr_number ON companies(cvr_number);

-- Create sync history table
CREATE TABLE sync_history (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INTEGER NOT NULL,
    status VARCHAR(50) NOT NULL,
    message TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
);

-- Insert test data
INSERT INTO companies (cvr_number, name) VALUES 
    ('37609110', 'Mercura ApS'),
    ('26616409', 'ServicePoint A/S'),
    ('36903341', 'girafpingvin ApS'),
    ('36598301', 'Den Italienske Isbutik'),
    ('28856636', 'ÅRHUS ApS'),
    ('41461098', 'Ost ApS'),
    ('32365469', 'Mosevang Mælk ApS');