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

