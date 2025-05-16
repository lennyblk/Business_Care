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

create table advice_category
(
    id          int auto_increment
        primary key,
    name        varchar(100)                           not null,
    description text                                   null,
    is_active   tinyint(1) default 1                   null,
    created_at  datetime   default current_timestamp() null
);

create table advice
(
    id              int auto_increment
        primary key,
    title           varchar(255)                                          not null,
    content         text                                                  not null,
    category_id     int                                                   not null,
    publish_date    date                                                  not null,
    expiration_date date                                                  null,
    is_personalized tinyint(1)                default 0                   null,
    min_formule     enum ('Basic', 'Premium') default 'Basic'             null,
    is_published    tinyint(1)                default 0                   null,
    created_at      datetime                  default current_timestamp() null,
    updated_at      datetime                  default current_timestamp() null on update current_timestamp(),
    constraint advice_ibfk_1
        foreign key (category_id) references advice_category (id)
);

create table advice_media
(
    id          int auto_increment
        primary key,
    advice_id   int                                          not null,
    media_type  enum ('image', 'video', 'document', 'other') not null,
    media_url   varchar(255)                                 not null,
    title       varchar(100)                                 null,
    description text                                         null,
    created_at  datetime default current_timestamp()         null,
    constraint advice_media_ibfk_1
        foreign key (advice_id) references advice (id)
            on delete cascade
);

create table advice_schedule
(
    id              int auto_increment
        primary key,
    advice_id       int                                    not null,
    scheduled_date  date                                   not null,
    is_sent         tinyint(1)               default 0     null,
    sent_at         datetime                               null,
    target_audience enum ('All', 'Specific') default 'All' null,
    target_criteria text                                   null comment 'JSON encoded targeting criteria if specific',
    constraint advice_schedule_ibfk_1
        foreign key (advice_id) references advice (id)
);

create table advice_tag
(
    id   int auto_increment
        primary key,
    name varchar(50) not null,
    constraint unique_tag_name
        unique (name)
);

create table advice_has_tag
(
    advice_id int not null,
    tag_id    int not null,
    primary key (advice_id, tag_id),
    constraint advice_has_tag_ibfk_1
        foreign key (advice_id) references advice (id)
            on delete cascade,
    constraint advice_has_tag_ibfk_2
        foreign key (tag_id) references advice_tag (id)
            on delete cascade
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
    id                     int auto_increment
        primary key,
    company_id             int                                                                  not null,
    start_date             date                                                                 not null,
    end_date               date                                                                 not null,
    services               text                                                                 not null,
    amount                 decimal(10, 2)                                                       not null,
    payment_method         enum ('Direct Debit', 'Invoice')                                     not null,
    formule_abonnement     enum ('Starter', 'Basic', 'Premium')               default 'Starter' null,
    stripe_checkout_id     varchar(255)                                                         null,
    stripe_subscription_id varchar(255)                                                         null,
    payment_status         enum ('pending', 'unpaid', 'processing', 'active') default 'pending' null
);

create table employee
(
    id                          int auto_increment
        primary key,
    company_id                  int                           null,
    first_name                  varchar(50)                   not null,
    last_name                   varchar(50)                   not null,
    email                       varchar(255)                  not null,
    telephone                   varchar(20)                   null,
    position                    varchar(100)                  not null,
    departement                 varchar(100)                  null,
    date_creation_compte        date        default curdate() null,
    password                    varchar(255)                  not null,
    derniere_connexion          datetime                      null,
    preferences_langue          varchar(10) default 'fr'      null,
    advice_notification_enabled tinyint(1)  default 1         null,
    id_carte_nfc                varchar(50)                   null,
    constraint email
        unique (email),
    constraint employee_ibfk_1
        foreign key (company_id) references company (id)
);

create table advice_feedback
(
    id          int auto_increment
        primary key,
    employee_id int                                  not null,
    advice_id   int                                  not null,
    rating      int                                  not null comment 'Rating from 1 to 5',
    comment     text                                 null,
    is_helpful  tinyint(1)                           null,
    created_at  datetime default current_timestamp() null,
    constraint unique_employee_advice_feedback
        unique (employee_id, advice_id),
    constraint advice_feedback_ibfk_1
        foreign key (employee_id) references employee (id),
    constraint advice_feedback_ibfk_2
        foreign key (advice_id) references advice (id)
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

create table employee_advice_preference
(
    id                   int auto_increment
        primary key,
    employee_id          int                                  not null,
    preferred_categories text                                 null comment 'JSON encoded list of preferred categories',
    preferred_tags       text                                 null comment 'JSON encoded list of preferred tags',
    interests            text                                 null comment 'JSON encoded list of interests',
    created_at           datetime default current_timestamp() null,
    updated_at           datetime default current_timestamp() null on update current_timestamp(),
    constraint unique_employee_preference
        unique (employee_id),
    constraint employee_advice_preference_ibfk_1
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

create table location
(
    id          int auto_increment
        primary key,
    name        varchar(255)                            not null,
    address     varchar(255)                            not null,
    postal_code varchar(10)                             not null,
    city        varchar(100)                            not null,
    country     varchar(50) default 'France'            null,
    is_active   tinyint(1)  default 1                   null,
    created_at  datetime    default current_timestamp() null
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
    user_type       varchar(255)                                                                                                                  not null comment 'societe, employe, prestataire',
    company_name    varchar(255)                                                                                                                  null,
    first_name      varchar(255)                                                                                                                  null,
    last_name       varchar(255)                                                                                                                  null,
    email           varchar(255)                                                                                                                  not null,
    password        varchar(255)                                                                                                                  not null,
    telephone       varchar(255)                                                                                                                  null,
    position        varchar(255)                                                                                                                  null,
    departement     varchar(255)                                                                                                                  null,
    address         varchar(255)                                                                                                                  null,
    code_postal     varchar(20)                                                                                                                   null,
    ville           varchar(255)                                                                                                                  null,
    siret           varchar(14)                                                                                                                   null,
    description     text                                                                                                                          null,
    domains         varchar(255)                                                                                                                  null,
    activity_type   enum ('rencontre sportive', 'conférence', 'webinar', 'yoga', 'pot', 'séance d''art plastiques', 'session jeu vidéo', 'autre') null,
    tarif_horaire   decimal(10, 2)                                                                                                                null,
    additional_data longtext collate utf8mb4_bin                                                                                                  null
        check (json_valid(`additional_data`)),
    status          varchar(20) default 'pending'                                                                                                 not null comment 'pending, approved, rejected',
    created_at      timestamp                                                                                                                     null,
    updated_at      timestamp                                                                                                                     null
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

create table personalized_advice
(
    advice_id            int  not null
        primary key,
    target_criteria      text not null comment 'JSON encoded criteria for targeting',
    suggested_activities text null comment 'JSON encoded list of suggested activities',
    constraint personalized_advice_ibfk_1
        foreign key (advice_id) references advice (id)
            on delete cascade
);

create table provider
(
    id                   int auto_increment
        primary key,
    last_name            varchar(100)                                                                                                                  null,
    first_name           varchar(100)                                                                                                                  null,
    description          text                                                                                                                          not null,
    rating               decimal(3, 2)                          default 0.00                                                                           null,
    domains              text                                                                                                                          not null,
    email                varchar(255)                                                                                                                  not null,
    telephone            varchar(20)                                                                                                                   null,
    password             varchar(255)                                                                                                                  not null,
    adresse              varchar(255)                                                                                                                  null,
    code_postal          varchar(10)                                                                                                                   null,
    ville                varchar(100)                                                                                                                  null,
    siret                varchar(14)                                                                                                                   null,
    iban                 varchar(34)                                                                                                                   null,
    statut_prestataire   enum ('Candidat', 'Validé', 'Inactif') default 'Candidat'                                                                     null,
    date_validation      date                                                                                                                          null,
    validation_documents text                                                                                                                          null,
    tarif_horaire        decimal(10, 2)                                                                                                                null,
    nombre_evaluations   int                                    default 0                                                                              null,
    activity_type        enum ('rencontre sportive', 'conférence', 'webinar', 'yoga', 'pot', 'séance d''art plastiques', 'session jeu vidéo', 'autre') not null,
    other_activity       varchar(255)                                                                                                                  null,
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

create table event_proposal
(
    id            int auto_increment
        primary key,
    company_id    int                                                                                           not null,
    event_type_id int                                                                                           not null,
    proposed_date date                                                                                          not null,
    location_id   int                                                                                           not null,
    status        enum ('Pending', 'Assigned', 'Accepted', 'Rejected', 'Completed') default 'Pending'           null,
    notes         text                                                                                          null,
    created_at    datetime                                                          default current_timestamp() null,
    updated_at    datetime                                                          default current_timestamp() null on update current_timestamp(),
    duration      int                                                               default 60                  not null comment 'Durée en minutes',
    constraint fk_event_proposal_company
        foreign key (company_id) references company (id),
    constraint fk_event_proposal_event_type
        foreign key (event_type_id) references service_type (id)
);

create table event
(
    id                int                                                       not null
        primary key,
    name              varchar(255)                                              not null,
    description       text                                                      null,
    date              datetime                                                  not null,
    event_type        enum ('Webinar', 'Conference', 'Sport Event', 'Workshop') not null,
    capacity          int                                                       not null,
    location          varchar(255)                                              null,
    registrations     int default 0                                             not null,
    company_id        int                                                       null,
    event_proposal_id int                                                       null,
    duration          int default 60                                            not null comment 'Durée en minutes',
    constraint fk_event_company
        foreign key (company_id) references company (id),
    constraint fk_event_event_proposal
        foreign key (event_proposal_id) references event_proposal (id)
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

create table provider_assignment
(
    id                int auto_increment
        primary key,
    event_proposal_id int                                                                   not null,
    provider_id       int                                                                   not null,
    status            enum ('Proposed', 'Accepted', 'Rejected') default 'Proposed'          null,
    proposed_at       datetime                                  default current_timestamp() null,
    response_at       datetime                                                              null,
    payment_amount    decimal(10, 2)                                                        not null,
    constraint fk_provider_assignment_event_proposal
        foreign key (event_proposal_id) references event_proposal (id),
    constraint fk_provider_assignment_provider
        foreign key (provider_id) references provider (id)
);

create table provider_availability
(
    id                     int auto_increment
        primary key,
    provider_id            int                                                            not null,
    date_available         date                                                           not null,
    start_time             time                                                           not null,
    end_time               time                                                           not null,
    status                 enum ('Available', 'Reserved', 'Canceled') default 'Available' null,
    provider_assignment_id int                                                            null,
    constraint fk_provider_availability_assignment
        foreign key (provider_assignment_id) references provider_assignment (id),
    constraint provider_availability_ibfk_1
        foreign key (provider_id) references provider (id)
);

create table provider_recommendation_log
(
    id                 int auto_increment
        primary key,
    event_proposal_id  int                                       not null,
    provider_id        int                                       not null,
    geographic_match   tinyint(1)    default 0                   null,
    skill_match        tinyint(1)    default 0                   null,
    rating_score       decimal(5, 2) default 0.00                null,
    price_score        decimal(5, 2) default 0.00                null,
    availability_score decimal(5, 2) default 0.00                null,
    total_score        decimal(5, 2) default 0.00                null,
    recommended        tinyint(1)    default 0                   null,
    created_at         datetime      default current_timestamp() null,
    constraint fk_provider_recommendation_event_proposal
        foreign key (event_proposal_id) references event_proposal (id),
    constraint fk_provider_recommendation_provider
        foreign key (provider_id) references provider (id)
);

create table service_evaluation
(
    id              int auto_increment
        primary key,
    event_id        int                                  not null,
    employee_id     int                                  not null,
    rating          decimal(3, 2)                        not null,
    comment         text                                 null,
    evaluation_date datetime default current_timestamp() null,
    constraint fk_service_evaluation_event
        foreign key (event_id) references event (id)
            on update cascade on delete cascade,
    constraint service_evaluation_ibfk_2
        foreign key (employee_id) references employee (id)
);

create index service_evaluation_event_id_index
    on service_evaluation (event_id);

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

