
CREATE DATABASE IF NOT EXISTS client_management_system;
USE client_management_system;


DROP TABLE IF EXISTS documents;
DROP TABLE IF EXISTS contracts;
DROP TABLE IF EXISTS clients;
DROP TABLE IF EXISTS users;


CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','employee') DEFAULT 'employee',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    phone VARCHAR(50),
    company VARCHAR(255),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE contracts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT,
    contract_number VARCHAR(50) NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    contract_type ENUM('Régie','Forfait','Maintenance_et_Support','Vente_Marchandises','Prestation_Services','Partenariat_Commercial','NDA') NOT NULL,
    amount DECIMAL(10,2),
    currency ENUM('MAD','EUR','USD') DEFAULT 'MAD',
    tax_required BOOLEAN DEFAULT TRUE,
    tax_rate DECIMAL(5,2) DEFAULT 20.00,
    payment_terms VARCHAR(100),
    signature_date DATE,
    expiry_date DATE,
    renewal_terms VARCHAR(200),
    legal_representative VARCHAR(100),
    registration_number VARCHAR(50),
    status ENUM('Draft','Active','Completed','Terminated','Expired') DEFAULT 'Draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
);

CREATE TABLE documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contract_id INT,
    type VARCHAR(50),
    file_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (contract_id) REFERENCES contracts(id) ON DELETE CASCADE
);




INSERT INTO users (username, password, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('achraf', '$2y$10$oCYeLc/vUDx3pa6Jg7SHgOvl0HawnPyxHzduykRCXolfloyIErQNi', 'employee');


INSERT INTO clients (name, email, phone, company) VALUES 
('Entreprise ABC', 'contact@abc.ma', '0612345678', 'ABC SARL'),
('Société XYZ', 'info@xyz.ma', '0623456789', 'XYZ SARL'),
('Cabinet Consulting', 'consulting@cabinet.ma', '0634567890', 'Cabinet Consulting SARL');

INSERT INTO contracts (client_id, contract_number, name, description, contract_type, amount, currency, tax_required, tax_rate, payment_terms, status) VALUES 
(1, 'CON-2024-001', 'Contrat de vente de matériel informatique', 'Vente de 50 ordinateurs portables à l\'école ABC', 'Vente_Marchandises', 150000.00, 'MAD', 1, 20.00, 'Paiement à 30 jours', 'Active'),
(1, 'CON-2024-002', 'Contrat de prestation de services', 'Services de maintenance informatique et support technique', 'Prestation_Services', 50000.00, 'MAD', 1, 20.00, 'Paiement mensuel', 'Active'),
(2, 'CON-2024-003', 'Contrat de partenariat commercial', 'Partenariat pour distribution de produits dans la région de Fès', 'Partenariat_Commercial', 75000.00, 'MAD', 1, 20.00, 'Paiement trimestriel', 'Draft'),
(3, 'CON-2024-004', 'Contrat de prestation indépendante', 'Services de conseil en gestion d\'entreprise', 'Prestation_Independante', 25000.00, 'MAD', 1, 20.00, 'Paiement à 15 jours', 'Active'),
(1, 'CON-2024-005', 'Bail commercial', 'Location de bureau au centre-ville de Casablanca', 'Bail_Commercial', 12000.00, 'MAD', 1, 20.00, 'Paiement mensuel', 'Active'),
(2, 'CON-2024-006', 'Contrat de travail CDI', 'Embauche d\'un commercial', 'Travail_CDI', 8000.00, 'MAD', 1, 20.00, 'Paiement mensuel', 'Active');

INSERT INTO documents (contract_id, type, file_path) VALUES 
(1, 'Contrat', 'uploads/contrat_vente_marchandises_001.pdf'),
(1, 'Facture', 'uploads/facture_vente_marchandises_001.pdf'),
(2, 'Contrat', 'uploads/contrat_prestation_services_002.pdf'),
(2, 'Facture', 'uploads/facture_prestation_services_002.pdf'),
(3, 'Contrat', 'uploads/contrat_partenariat_003.pdf'),
(3, 'Facture', 'uploads/facture_partenariat_003.pdf'),
(4, 'Contrat', 'uploads/contrat_prestation_independante_004.pdf'),
(4, 'Facture', 'uploads/facture_prestation_independante_004.pdf'),
(5, 'Contrat', 'uploads/bail_commercial_005.pdf'),
(6, 'Contrat', 'uploads/contrat_travail_cdi_006.pdf');

CREATE INDEX idx_contract_number ON contracts(contract_number);
CREATE INDEX idx_contract_type ON contracts(contract_type);
CREATE INDEX idx_contract_status ON contracts(status);
CREATE INDEX idx_client_name ON clients(name);
CREATE INDEX idx_user_username ON users(username);

ALTER TABLE documents MODIFY contract_id INT NULL;