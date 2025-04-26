create table admin
(
    id       int auto_increment
        primary key,
    email    varchar(255)                not null,
    password varchar(255)                not null,
    name     varchar(100)                not null,
    type     varchar(10) default 'admin' not null,
    constraint email
        unique (email)
);

create table anonymous_report
(
    id                    int auto_increment
        primary key,
    encrypted_employee_id varchar(255)                                                       not null,
    report_date           datetime                               default current_timestamp() null,
    description           text                                                               not null,
    category              varchar(100)                                                       not null,
    status                enum ('New', 'Processing', 'Resolved') default 'New'               null,
    severity_level        int                                    default 1                   null
);

create table association
(
    id           int auto_increment
        primary key,
    name         varchar(255)                                 not null,
    description  text                                         not null,
    domain       varchar(100)                                 not null,
    contact_info varchar(255)                                 not null,
    website      varchar(255)                                 null,
    status       enum ('Active', 'Inactive') default 'Active' null
);

create table community
(
    id          int auto_increment
        primary key,
    name        varchar(255)                  not null,
    description text                          not null,
    type        enum ('Internal', 'External') not null
);

create table company
(
    id                    int auto_increment
        primary key,
    name                  varchar(255)                                           not null,
    address               varchar(255)                                           not null,
    code_postal           varchar(10)                                            null,
    ville                 varchar(100)                                           null,
    pays                  varchar(100)                         default 'France'  null,
    telephone             varchar(20)                                            null,
    creation_date         date                                                   not null,
    email                 varchar(255)                                           not null,
    password              varchar(255)                                           not null,
    siret                 varchar(14)                                            null,
    formule_abonnement    enum ('Starter', 'Basic', 'Premium') default 'Starter' null,
    statut_compte         enum ('Actif', 'Inactif')            default 'Actif'   null,
    date_debut_contrat    date                                 default curdate() null,
    date_fin_contrat      date                                                   null,
    mode_paiement_prefere varchar(50)                                            null,
    employee_count        int                                  default 0         null,
    constraint email
        unique (email)
);

create table contract
(
    id                 int auto_increment
        primary key,
    company_id         int                                                                                  not null,
    start_date         date                                                                                 not null,
    end_date           date                                                                                 not null,
    services           text                                                                                 not null,
    amount             decimal(10, 2)                                                                       not null,
    payment_method     enum ('Direct Debit', 'Invoice')                                                     not null,
    formule_abonnement enum ('Starter', 'Basic', 'Premium')                default 'Starter'                null,
    statut_contrat     enum ('Actif', 'Inactif', 'En cours de validation') default 'En cours de validation' null,
    constraint contract_ibfk_1
        foreign key (company_id) references company (id)
);

create index company_id
    on contract (company_id);

create table employee
(
    id                   int auto_increment
        primary key,
    company_id           int                           null,
    first_name           varchar(50)                   not null,
    last_name            varchar(50)                   not null,
    email                varchar(255)                  not null,
    telephone            varchar(20)                   null,
    position             varchar(100)                  not null,
    departement          varchar(100)                  null,
    date_creation_compte date        default curdate() null,
    password             varchar(255)                  not null,
    derniere_connexion   datetime                      null,
    preferences_langue   varchar(10) default 'fr'      null,
    id_carte_nfc         varchar(50)                   null,
    constraint email
        unique (email),
    constraint employee_ibfk_1
        foreign key (company_id) references company (id)
);

create table chatbot_question
(
    id            int auto_increment
        primary key,
    employee_id   int                                                         not null,
    question      text                                                        not null,
    response      text                                                        null,
    question_date datetime                        default current_timestamp() null,
    status        enum ('Resolved', 'Unresolved') default 'Unresolved'        null,
    constraint chatbot_question_ibfk_1
        foreign key (employee_id) references employee (id)
);

create table donation
(
    id                    int auto_increment
        primary key,
    association_id        int                                                       not null,
    employee_id           int                                                       not null,
    donation_type         enum ('Financial', 'Material', 'Time')                    not null,
    amount_or_description text                                                      not null,
    donation_date         datetime                      default current_timestamp() null,
    status                enum ('Pending', 'Validated') default 'Pending'           null,
    constraint donation_ibfk_1
        foreign key (association_id) references association (id),
    constraint donation_ibfk_2
        foreign key (employee_id) references employee (id)
);

create table employee_community
(
    employee_id  int not null,
    community_id int not null,
    primary key (employee_id, community_id),
    constraint employee_community_ibfk_1
        foreign key (employee_id) references employee (id),
    constraint employee_community_ibfk_2
        foreign key (community_id) references community (id)
);

create index community_id
    on employee_community (community_id);

create table event
(
    id            int auto_increment
        primary key,
    name          varchar(255)                                              not null,
    description   text                                                      null,
    date          datetime                                                  not null,
    event_type    enum ('Webinar', 'Conference', 'Sport Event', 'Workshop') not null,
    capacity      int                                                       not null,
    location      varchar(255)                                              null,
    registrations int default 0                                             not null,
    company_id    int                                                       null,
    constraint fk_event_company
        foreign key (company_id) references company (id)
);

create table event_registration
(
    id                int auto_increment
        primary key,
    event_id          int                                                                   not null,
    employee_id       int                                                                   not null,
    registration_date datetime                                  default current_timestamp() null,
    status            enum ('Confirmed', 'Canceled', 'Waiting') default 'Confirmed'         null,
    constraint event_registration_ibfk_1
        foreign key (event_id) references event (id),
    constraint event_registration_ibfk_2
        foreign key (employee_id) references employee (id)
);

create table failed_jobs
(
    id         bigint unsigned auto_increment
        primary key,
    uuid       varchar(255)                          not null,
    connection text                                  not null,
    queue      text                                  not null,
    payload    longtext                              not null,
    exception  longtext                              not null,
    failed_at  timestamp default current_timestamp() not null,
    constraint failed_jobs_uuid_unique
        unique (uuid)
)
    collate = utf8mb4_unicode_ci;

create table invoice
(
    id             int auto_increment
        primary key,
    company_id     int                                                   not null,
    contract_id    int                                                   null,
    issue_date     date                                                  not null,
    due_date       date                                                  not null,
    total_amount   decimal(10, 2)                                        not null,
    payment_status enum ('Pending', 'Paid', 'Overdue') default 'Pending' null,
    pdf_path       varchar(255)                                          null,
    details        text                                                  not null,
    constraint invoice_ibfk_1
        foreign key (company_id) references company (id),
    constraint invoice_ibfk_2
        foreign key (contract_id) references contract (id)
);

create table migrations
(
    id        int unsigned auto_increment
        primary key,
    migration varchar(255) not null,
    batch     int          not null
)
    collate = utf8mb4_unicode_ci;

create table notification
(
    id                int auto_increment
        primary key,
    recipient_id      int                                                          not null,
    recipient_type    enum ('Company', 'Employee', 'Provider')                     not null,
    title             varchar(255)                                                 not null,
    message           text                                                         not null,
    creation_date     datetime                         default current_timestamp() null,
    send_date         datetime                                                     null,
    status            enum ('Pending', 'Sent', 'Read') default 'Pending'           null,
    notification_type enum ('Email', 'Push', 'Internal')                           not null
);

create table password_resets
(
    email      varchar(255) not null,
    token      varchar(255) not null,
    created_at timestamp    null
)
    collate = utf8mb4_unicode_ci;

create index password_resets_email_index
    on password_resets (email);

create table pending_registrations
(
    id              bigint unsigned auto_increment
        primary key,
    user_type       varchar(255)                  not null comment 'societe, employe, prestataire',
    company_name    varchar(255)                  null,
    first_name      varchar(255)                  null,
    last_name       varchar(255)                  null,
    email           varchar(255)                  not null,
    password        varchar(255)                  not null,
    telephone       varchar(255)                  null,
    position        varchar(255)                  null,
    departement     varchar(255)                  null,
    address         varchar(255)                  null,
    code_postal     varchar(20)                   null,
    ville           varchar(255)                  null,
    siret           varchar(14)                   null,
    description     text                          null,
    domains         varchar(255)                  null,
    tarif_horaire   decimal(10, 2)                null,
    additional_data longtext collate utf8mb4_bin  null
        check (json_valid(`additional_data`)),
    status          varchar(20) default 'pending' not null comment 'pending, approved, rejected',
    created_at      timestamp                     null,
    updated_at      timestamp                     null
)
    collate = utf8mb4_unicode_ci;

create index pending_registrations_email_index
    on pending_registrations (email);

create index pending_registrations_status_index
    on pending_registrations (status);

create table personal_access_tokens
(
    id             bigint unsigned auto_increment
        primary key,
    tokenable_type varchar(255)    not null,
    tokenable_id   bigint unsigned not null,
    name           varchar(255)    not null,
    token          varchar(64)     not null,
    abilities      text            null,
    last_used_at   timestamp       null,
    created_at     timestamp       null,
    updated_at     timestamp       null,
    constraint personal_access_tokens_token_unique
        unique (token)
)
    collate = utf8mb4_unicode_ci;

create index personal_access_tokens_tokenable_type_tokenable_id_index
    on personal_access_tokens (tokenable_type, tokenable_id);

create table provider
(
    id                   int auto_increment
        primary key,
    last_name            varchar(100)                                              null,
    first_name           varchar(100)                                              null,
    description          text                                                      not null,
    rating               decimal(3, 2)                          default 0.00       null,
    domains              text                                                      not null,
    email                varchar(255)                                              not null,
    telephone            varchar(20)                                               null,
    password             varchar(255)                                              not null,
    adresse              varchar(255)                                              null,
    code_postal          varchar(10)                                               null,
    ville                varchar(100)                                              null,
    siret                varchar(14)                                               null,
    iban                 varchar(34)                                               null,
    statut_prestataire   enum ('Candidat', 'Validé', 'Inactif') default 'Candidat' null,
    date_validation      date                                                      null,
    validation_documents text                                                      null,
    tarif_horaire        decimal(10, 2)                                            null,
    nombre_evaluations   int                                    default 0          null,
    constraint email
        unique (email)
);

create table medical_appointment
(
    id               int auto_increment
        primary key,
    employee_id      int                  not null,
    provider_id      int                  not null,
    appointment_date datetime             not null,
    reason           text                 not null,
    confidential     tinyint(1) default 1 not null,
    constraint medical_appointment_ibfk_1
        foreign key (employee_id) references employee (id),
    constraint medical_appointment_ibfk_2
        foreign key (provider_id) references provider (id)
);

create index employee_id
    on medical_appointment (employee_id);

create index provider_id
    on medical_appointment (provider_id);

create table provider_availability
(
    id             int auto_increment
        primary key,
    provider_id    int                                                            not null,
    date_available date                                                           not null,
    start_time     time                                                           not null,
    end_time       time                                                           not null,
    status         enum ('Available', 'Reserved', 'Canceled') default 'Available' null,
    constraint provider_availability_ibfk_1
        foreign key (provider_id) references provider (id)
);

create table provider_invoice
(
    id             int auto_increment
        primary key,
    provider_id    int                                        not null,
    month          int                                        not null,
    year           int                                        not null,
    total_amount   decimal(10, 2)                             not null,
    payment_status enum ('Pending', 'Paid') default 'Pending' null,
    issue_date     date                                       not null,
    payment_date   date                                       null,
    pdf_path       varchar(255)                               null,
    constraint provider_invoice_ibfk_1
        foreign key (provider_id) references provider (id)
);

create table quote
(
    id                    int auto_increment
        primary key,
    company_id            int                                                        not null,
    creation_date         date                                                       not null,
    expiration_date       date                                                       not null,
    company_size          int                                                        not null,
    formule_abonnement    enum ('Starter', 'Basic', 'Premium')     default 'Starter' not null,
    activities_count      int                                                        not null,
    medical_appointments  int                                                        not null,
    extra_appointment_fee decimal(5, 2)                                              not null,
    chatbot_questions     varchar(20)                                                not null,
    weekly_advice         tinyint(1)                                                 not null,
    personalized_advice   tinyint(1)                                                 not null,
    price_per_employee    decimal(6, 2)                                              not null,
    total_amount          decimal(10, 2)                                             not null,
    status                enum ('Pending', 'Accepted', 'Rejected') default 'Pending' null,
    services_details      text                                                       not null,
    constraint quote_ibfk_1
        foreign key (company_id) references company (id)
);

create table service_type
(
    id          int auto_increment
        primary key,
    provider_id int            not null,
    title       varchar(255)   not null,
    description text           not null,
    price       decimal(10, 2) not null,
    duration    int            not null,
    constraint service_type_ibfk_1
        foreign key (provider_id) references provider (id)
);

create table intervention
(
    id                int auto_increment
        primary key,
    provider_id       int                                                         not null,
    service_type_id   int                                                         not null,
    employee_id       int                                                         not null,
    intervention_date date                                                        not null,
    start_time        time                                                        not null,
    end_time          time                                                        not null,
    location          varchar(255)                                                not null,
    status            enum ('Planned', 'Completed', 'Canceled') default 'Planned' null,
    notes             text                                                        null,
    constraint intervention_ibfk_1
        foreign key (provider_id) references provider (id),
    constraint intervention_ibfk_2
        foreign key (service_type_id) references service_type (id),
    constraint intervention_ibfk_3
        foreign key (employee_id) references employee (id)
);

create table service_evaluation
(
    id              int auto_increment
        primary key,
    intervention_id int                                  not null,
    employee_id     int                                  not null,
    rating          decimal(3, 2)                        not null,
    comment         text                                 null,
    evaluation_date datetime default current_timestamp() null,
    constraint service_evaluation_ibfk_1
        foreign key (intervention_id) references intervention (id),
    constraint service_evaluation_ibfk_2
        foreign key (employee_id) references employee (id)
);

create index provider_id
    on service_type (provider_id);

create table translations
(
    id              int auto_increment
        primary key,
    translation_key varchar(255) not null,
    language        varchar(10)  not null,
    text            text         not null,
    constraint unique_translation
        unique (translation_key, language)
);

create table users
(
    id                bigint unsigned auto_increment
        primary key,
    name              varchar(255) not null,
    email             varchar(255) not null,
    email_verified_at timestamp    null,
    password          varchar(255) not null,
    remember_token    varchar(100) null,
    created_at        timestamp    null,
    updated_at        timestamp    null,
    constraint users_email_unique
        unique (email)
)
    collate = utf8mb4_unicode_ci;

