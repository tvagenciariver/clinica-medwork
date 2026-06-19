-- script de criação do banco de dados (init.sql)
-- Para executar: importe este arquivo no phpMyAdmin ou via linha de comando: mysql -u user -p database < init.sql

-- Criação da tabela companies
CREATE TABLE IF NOT EXISTS companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    corporate_name VARCHAR(150) NOT NULL,
    trade_name VARCHAR(150),
    cnpj VARCHAR(18) UNIQUE NOT NULL,
    manager_name VARCHAR(100),
    main_phone VARCHAR(20),
    has_whatsapp BOOLEAN DEFAULT FALSE,
    email VARCHAR(100),
    address TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    observations TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Criação da tabela patients
CREATE TABLE IF NOT EXISTS patients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    cpf VARCHAR(14) UNIQUE NOT NULL,
    birth_date DATE,
    gender ENUM('M', 'F', 'Other'),
    main_phone VARCHAR(20),
    has_whatsapp BOOLEAN DEFAULT FALSE,
    email VARCHAR(100),
    address TEXT,
    default_company_id INT NULL,
    observations TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (default_company_id) REFERENCES companies(id) ON DELETE SET NULL
);

-- Criação da tabela users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL, -- Login
    password VARCHAR(255) NOT NULL, -- Hash
    role ENUM('admin', 'employee', 'patient', 'company') NOT NULL,
    patient_id INT NULL,
    company_id INT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    last_access TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
);

-- Inserir usuário administrador padrão (senha: admin123)
INSERT INTO users (name, email, password, role) 
VALUES ('Administrador Master', 'admin@clinica.com.br', '$2y$10$w0ldXxahcAkU22BTYCSzkODqJFXIQ0J2FHugHqxkYP8aCT3PAKxza', 'admin')
ON DUPLICATE KEY UPDATE password=VALUES(password);

-- Criação da tabela exams
CREATE TABLE IF NOT EXISTS exams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    origin ENUM('company', 'private') NOT NULL,
    company_id INT NULL, -- Requerido se origin = 'company'
    exam_type VARCHAR(100) NOT NULL,
    exam_date DATE NOT NULL,
    responsible_doctor VARCHAR(100),
    file_path VARCHAR(255),
    observations TEXT,
    status ENUM('registered', 'processing', 'available', 'sent_whatsapp', 'viewed_patient', 'viewed_company') DEFAULT 'registered',
    available_at TIMESTAMP NULL,
    created_by INT,
    protocol_code VARCHAR(50) UNIQUE NOT NULL,
    allow_whatsapp BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Criação da tabela message_logs
CREATE TABLE IF NOT EXISTS message_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    exam_id INT,
    patient_id INT NULL,
    company_id INT NULL,
    recipient_type ENUM('patient', 'company') NOT NULL,
    destination_phone VARCHAR(20) NOT NULL,
    channel VARCHAR(20) DEFAULT 'whatsapp',
    message_sent TEXT NOT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('success', 'error') NOT NULL,
    api_response TEXT,
    sent_by INT,
    FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE SET NULL,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE SET NULL,
    FOREIGN KEY (sent_by) REFERENCES users(id) ON DELETE SET NULL
);
