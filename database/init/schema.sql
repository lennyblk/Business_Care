-- Création de la table admin
CREATE TABLE admin (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    email    VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    name     VARCHAR(100) NOT NULL,
    type     VARCHAR(10) NOT NULL DEFAULT 'admin',
    CONSTRAINT email UNIQUE (email)
);

-- Création de la table anonymous_report
CREATE TABLE anonymous_report (
    id                    INT AUTO_INCREMENT PRIMARY KEY,
    encrypted_employee_id VARCHAR(255) NOT NULL,
    report_date           DATETIME DEFAULT CURRENT_TIMESTAMP() NULL,
    description           TEXT NOT NULL,
    category              VARCHAR(100) NOT NULL,
    status                ENUM ('New', 'Processing', 'Resolved') DEFAULT 'New' NULL,
    severity_level        INT DEFAULT 1 NULL
);

-- Création de la table association
CREATE TABLE association (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(255) NOT NULL,
    description  TEXT NOT NULL,
    domain       VARCHAR(100) NOT NULL,
    contact_info VARCHAR(255) NOT NULL,
    website      VARCHAR(255) NULL,
    status       ENUM ('Active', 'Inactive') DEFAULT 'Active' NULL
);

-- Création de la table community
CREATE TABLE community (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    type        ENUM ('Internal', 'External') NOT NULL
);

-- Création de la table company
CREATE TABLE company (
    id                    INT AUTO_INCREMENT PRIMARY KEY,
    name                  VARCHAR(255) NOT NULL,
    address               VARCHAR(255) NOT NULL,
    code_postal           VARCHAR(10) NULL,
    ville                 VARCHAR(100) NULL,
    pays                  VARCHAR(100) DEFAULT 'France' NULL,
    telephone             VARCHAR(20) NOT NULL,
    creation_date         DATE NOT NULL,
    email                 VARCHAR(255) NOT NULL,
    password              VARCHAR(255) NOT NULL,
    siret                 VARCHAR(14) NULL,
    formule_abonnement    ENUM ('Starter', 'Basic', 'Premium') DEFAULT 'Starter' NULL,
    statut_compte         ENUM ('Actif', 'Inactif') DEFAULT 'Actif' NULL,
    date_debut_contrat    DATE DEFAULT CURDATE() NULL,
    date_fin_contrat      DATE NULL,
    mode_paiement_prefere VARCHAR(50) NULL,
    employee_count        INT DEFAULT 0 NULL,
    CONSTRAINT email UNIQUE (email)
);

-- Création de la table contract
CREATE TABLE contract (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    company_id         INT NOT NULL,
    start_date         DATE NOT NULL,
    end_date           DATE NOT NULL,
    services           TEXT NOT NULL,
    amount             DECIMAL(10, 2) NOT NULL,
    payment_method     ENUM ('Direct Debit', 'Invoice') NOT NULL,
    formule_abonnement ENUM('Starter', 'Basic', 'Premium') DEFAULT 'Starter' NULL,
    statut_contrat     ENUM('Actif', 'Inactif', 'En cours de validation') DEFAULT 'En cours de validation' NULL,
    CONSTRAINT contract_ibfk_1 FOREIGN KEY (company_id) REFERENCES company (id)
);

CREATE INDEX company_id ON contract (company_id);

-- Création de la table employee
CREATE TABLE employee (
    id                   INT AUTO_INCREMENT PRIMARY KEY,
    company_id           INT NULL,
    first_name           VARCHAR(50) NOT NULL,
    last_name            VARCHAR(50) NOT NULL,
    email                VARCHAR(255) NOT NULL,
    telephone            VARCHAR(20) NULL,
    position             VARCHAR(100) NOT NULL,
    departement          VARCHAR(100) NULL,
    date_creation_compte DATE DEFAULT CURDATE() NULL,
    password             VARCHAR(255) NOT NULL,
    derniere_connexion   DATETIME NULL,
    preferences_langue   VARCHAR(10) DEFAULT 'fr' NULL,
    id_carte_nfc         VARCHAR(50) NULL,
    CONSTRAINT email UNIQUE (email),
    CONSTRAINT employee_ibfk_1 FOREIGN KEY (company_id) REFERENCES company (id)
);

-- Création de la table chatbot_question
CREATE TABLE chatbot_question (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    employee_id   INT NOT NULL,
    question      TEXT NOT NULL,
    response      TEXT NULL,
    question_date DATETIME DEFAULT CURRENT_TIMESTAMP() NULL,
    status        ENUM ('Resolved', 'Unresolved') DEFAULT 'Unresolved' NULL,
    CONSTRAINT chatbot_question_ibfk_1 FOREIGN KEY (employee_id) REFERENCES employee (id)
);

-- Création de la table donation
CREATE TABLE donation (
    id                    INT AUTO_INCREMENT PRIMARY KEY,
    association_id        INT NOT NULL,
    employee_id           INT NOT NULL,
    donation_type         ENUM ('Financial', 'Material', 'Time') NOT NULL,
    amount_or_description TEXT NOT NULL,
    donation_date         DATETIME DEFAULT CURRENT_TIMESTAMP() NULL,
    status                ENUM ('Pending', 'Validated') DEFAULT 'Pending' NULL,
    CONSTRAINT donation_ibfk_1 FOREIGN KEY (association_id) REFERENCES association (id),
    CONSTRAINT donation_ibfk_2 FOREIGN KEY (employee_id) REFERENCES employee (id)
);

-- Création de la table employee_community
CREATE TABLE employee_community (
    employee_id  INT NOT NULL,
    community_id INT NOT NULL,
    PRIMARY KEY (employee_id, community_id),
    CONSTRAINT employee_community_ibfk_1 FOREIGN KEY (employee_id) REFERENCES employee (id),
    CONSTRAINT employee_community_ibfk_2 FOREIGN KEY (community_id) REFERENCES community (id)
);

CREATE INDEX community_id ON employee_community (community_id);

-- Création de la table failed_jobs
CREATE TABLE failed_jobs (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid       VARCHAR(255) NOT NULL,
    connection TEXT NOT NULL,
    queue      TEXT NOT NULL,
    payload    LONGTEXT NOT NULL,
    exception  LONGTEXT NOT NULL,
    failed_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP() NOT NULL,
    CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid)
) COLLATE = utf8mb4_unicode_ci;

-- Création de la table provider
CREATE TABLE provider (
    id                   INT AUTO_INCREMENT PRIMARY KEY,
    last_name            VARCHAR(100) NULL,
    first_name           VARCHAR(100) NULL,
    description          TEXT NOT NULL,
    rating               DECIMAL(3, 2) DEFAULT 0.00 NULL,
    domains              TEXT NOT NULL,
    email                VARCHAR(255) NOT NULL,
    telephone            VARCHAR(20) NULL,
    password             VARCHAR(255) NOT NULL,
    adresse              VARCHAR(255) NULL,
    code_postal          VARCHAR(10) NULL,
    ville                VARCHAR(100) NULL,
    siret                VARCHAR(14) NULL,
    iban                 VARCHAR(34) NULL,
    statut_prestataire   ENUM ('Candidat', 'Validé', 'Inactif') DEFAULT 'Candidat' NULL,
    date_validation      DATE NULL,
    validation_documents TEXT NULL,
    tarif_horaire        DECIMAL(10, 2) NULL,
    nombre_evaluations   INT DEFAULT 0 NULL,
    CONSTRAINT email UNIQUE (email)
);

-- Création de la table event
CREATE TABLE event (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(255) NOT NULL,
    description   TEXT NULL,
    date          DATETIME NOT NULL,
    event_type    ENUM ('Webinar', 'Conference', 'Sport Event', 'Workshop') NOT NULL,
    capacity      INT NOT NULL,
    location      VARCHAR(255) NULL,
    registrations INT DEFAULT 0 NOT NULL,
    company_id    INT NULL,
    CONSTRAINT fk_event_company FOREIGN KEY (company_id) REFERENCES company(id)
);

-- Création de la table event_registration
CREATE TABLE event_registration (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    event_id          INT NOT NULL,
    employee_id       INT NOT NULL,
    registration_date DATETIME DEFAULT CURRENT_TIMESTAMP() NULL,
    status            ENUM ('Confirmed', 'Canceled', 'Waiting') DEFAULT 'Confirmed' NULL,
    CONSTRAINT event_registration_ibfk_1 FOREIGN KEY (event_id) REFERENCES event (id),
    CONSTRAINT event_registration_ibfk_2 FOREIGN KEY (employee_id) REFERENCES employee (id)
);

-- Création de la table invoice
CREATE TABLE invoice (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    company_id     INT NOT NULL,
    contract_id    INT NULL,
    issue_date     DATE NOT NULL,
    due_date       DATE NOT NULL,
    total_amount   DECIMAL(10, 2) NOT NULL,
    payment_status ENUM ('Pending', 'Paid', 'Overdue') DEFAULT 'Pending' NULL,
    pdf_path       VARCHAR(255) NULL,
    details        TEXT NOT NULL,
    CONSTRAINT invoice_ibfk_1 FOREIGN KEY (company_id) REFERENCES company (id),
    CONSTRAINT invoice_ibfk_2 FOREIGN KEY (contract_id) REFERENCES contract (id)
);

-- Création de la table medical_appointment
CREATE TABLE medical_appointment (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    employee_id      INT NOT NULL,
    provider_id      INT NOT NULL,
    appointment_date DATETIME NOT NULL,
    reason           TEXT NOT NULL,
    confidential     TINYINT(1) DEFAULT 1 NOT NULL,
    CONSTRAINT medical_appointment_ibfk_1 FOREIGN KEY (employee_id) REFERENCES employee (id),
    CONSTRAINT medical_appointment_ibfk_2 FOREIGN KEY (provider_id) REFERENCES provider (id)
);

CREATE INDEX employee_id ON medical_appointment (employee_id);
CREATE INDEX provider_id ON medical_appointment (provider_id);

-- Création de la table migrations
CREATE TABLE migrations (
    id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    migration VARCHAR(255) NOT NULL,
    batch     INT NOT NULL
) COLLATE = utf8mb4_unicode_ci;

-- Création de la table notification
CREATE TABLE notification (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    recipient_id      INT NOT NULL,
    recipient_type    ENUM ('Company', 'Employee', 'Provider') NOT NULL,
    title             VARCHAR(255) NOT NULL,
    message           TEXT NOT NULL,
    creation_date     DATETIME DEFAULT CURRENT_TIMESTAMP() NULL,
    send_date         DATETIME NULL,
    status            ENUM ('Pending', 'Sent', 'Read') DEFAULT 'Pending' NULL,
    notification_type ENUM ('Email', 'Push', 'Internal') NOT NULL
);

-- Création de la table password_resets
CREATE TABLE password_resets (
    email      VARCHAR(255) NOT NULL,
    token      VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL
) COLLATE = utf8mb4_unicode_ci;

CREATE INDEX password_resets_email_index ON password_resets (email);

-- Création de la table personal_access_tokens
CREATE TABLE personal_access_tokens (
    id             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tokenable_type VARCHAR(255) NOT NULL,
    tokenable_id   BIGINT UNSIGNED NOT NULL,
    name           VARCHAR(255) NOT NULL,
    token          VARCHAR(64) NOT NULL,
    abilities      TEXT NULL,
    last_used_at   TIMESTAMP NULL,
    created_at     TIMESTAMP NULL,
    updated_at     TIMESTAMP NULL,
    CONSTRAINT personal_access_tokens_token_unique UNIQUE (token)
) COLLATE = utf8mb4_unicode_ci;

CREATE INDEX personal_access_tokens_tokenable_type_tokenable_id_index ON personal_access_tokens (tokenable_type, tokenable_id);

-- Création de la table provider_availability
CREATE TABLE provider_availability (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    provider_id    INT NOT NULL,
    date_available DATE NOT NULL,
    start_time     TIME NOT NULL,
    end_time       TIME NOT NULL,
    status         ENUM ('Available', 'Reserved', 'Canceled') DEFAULT 'Available' NULL,
    CONSTRAINT provider_availability_ibfk_1 FOREIGN KEY (provider_id) REFERENCES provider (id)
);

-- Création de la table provider_invoice
CREATE TABLE provider_invoice (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    provider_id    INT NOT NULL,
    month          INT NOT NULL,
    year           INT NOT NULL,
    total_amount   DECIMAL(10, 2) NOT NULL,
    payment_status ENUM ('Pending', 'Paid') DEFAULT 'Pending' NULL,
    issue_date     DATE NOT NULL,
    payment_date   DATE NULL,
    pdf_path       VARCHAR(255) NULL,
    CONSTRAINT provider_invoice_ibfk_1 FOREIGN KEY (provider_id) REFERENCES provider (id)
);

-- Création de la table quote
CREATE TABLE quote (
    id                   INT AUTO_INCREMENT PRIMARY KEY,
    company_id           INT NOT NULL,
    creation_date        DATE NOT NULL,
    expiration_date      DATE NOT NULL,
    company_size         INT NOT NULL,
    formule_abonnement   ENUM('Starter', 'Basic', 'Premium') NOT NULL DEFAULT 'Starter',
    activities_count     INT NOT NULL,
    medical_appointments INT NOT NULL,
    extra_appointment_fee DECIMAL(5, 2) NOT NULL,
    chatbot_questions    VARCHAR(20) NOT NULL,
    weekly_advice        BOOLEAN NOT NULL,
    personalized_advice  BOOLEAN NOT NULL,
    price_per_employee   DECIMAL(6, 2) NOT NULL,
    total_amount         DECIMAL(10, 2) NOT NULL,
    status               ENUM ('Pending', 'Accepted', 'Rejected') DEFAULT 'Pending' NULL,
    services_details     TEXT NOT NULL,
    CONSTRAINT quote_ibfk_1 FOREIGN KEY (company_id) REFERENCES company (id)
);

-- Création de la table service_type
CREATE TABLE service_type (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    provider_id INT NOT NULL,
    title       VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    price       DECIMAL(10, 2) NOT NULL,
    duration    INT NOT NULL,
    CONSTRAINT service_type_ibfk_1 FOREIGN KEY (provider_id) REFERENCES provider (id)
);

CREATE INDEX provider_id ON service_type (provider_id);

-- Création de la table intervention
CREATE TABLE intervention (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    provider_id       INT NOT NULL,
    service_type_id   INT NOT NULL,
    employee_id       INT NOT NULL,
    intervention_date DATE NOT NULL,
    start_time        TIME NOT NULL,
    end_time          TIME NOT NULL,
    location          VARCHAR(255) NOT NULL,
    status            ENUM ('Planned', 'Completed', 'Canceled') DEFAULT 'Planned' NULL,
    notes             TEXT NULL,
    CONSTRAINT intervention_ibfk_1 FOREIGN KEY (provider_id) REFERENCES provider (id),
    CONSTRAINT intervention_ibfk_2 FOREIGN KEY (service_type_id) REFERENCES service_type (id),
    CONSTRAINT intervention_ibfk_3 FOREIGN KEY (employee_id) REFERENCES employee (id)
);

-- Création de la table service_evaluation
CREATE TABLE service_evaluation (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    intervention_id INT NOT NULL,
    employee_id     INT NOT NULL,
    rating          DECIMAL(3, 2) NOT NULL,
    comment         TEXT NULL,
    evaluation_date DATETIME DEFAULT CURRENT_TIMESTAMP() NULL,
    CONSTRAINT service_evaluation_ibfk_1 FOREIGN KEY (intervention_id) REFERENCES intervention (id),
    CONSTRAINT service_evaluation_ibfk_2 FOREIGN KEY (employee_id) REFERENCES employee (id)
);

-- Création de la table translations
CREATE TABLE translations (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    translation_key VARCHAR(255) NOT NULL,
    language        VARCHAR(10) NOT NULL,
    text            TEXT NOT NULL,
    CONSTRAINT unique_translation UNIQUE (translation_key, language)
);

-- Création de la table users
CREATE TABLE users (
    id                BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name              VARCHAR(255) NOT NULL,
    email             VARCHAR(255) NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password          VARCHAR(255) NOT NULL,
    remember_token    VARCHAR(100) NULL,
    created_at        TIMESTAMP NULL,
    updated_at        TIMESTAMP NULL,
    CONSTRAINT users_email_unique UNIQUE (email)
) COLLATE = utf8mb4_unicode_ci;

-- Création de la table pending_registrations
CREATE TABLE pending_registrations (
    id             BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    user_type      VARCHAR(255) NOT NULL COMMENT 'societe, employe, prestataire',
    company_name   VARCHAR(255) DEFAULT NULL,
    first_name     VARCHAR(255) DEFAULT NULL,
    last_name      VARCHAR(255) DEFAULT NULL,
    email          VARCHAR(255) NOT NULL,
    password       VARCHAR(255) NOT NULL,
    telephone      VARCHAR(255) DEFAULT NULL,
    position       VARCHAR(255) DEFAULT NULL,
    departement    VARCHAR(255) DEFAULT NULL,
    address        VARCHAR(255) DEFAULT NULL,
    code_postal    VARCHAR(20) DEFAULT NULL,
    ville          VARCHAR(255) DEFAULT NULL,
    siret          VARCHAR(14) DEFAULT NULL,
    description    TEXT DEFAULT NULL,
    domains        VARCHAR(255) DEFAULT NULL,
    tarif_horaire  DECIMAL(10,2) DEFAULT NULL,
    additional_data JSON DEFAULT NULL,
    status         VARCHAR(20) NOT NULL DEFAULT 'pending' COMMENT 'pending, approved, rejected',
    created_at     TIMESTAMP NULL DEFAULT NULL,
    updated_at     TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    KEY pending_registrations_email_index (email),
    KEY pending_registrations_status_index (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
