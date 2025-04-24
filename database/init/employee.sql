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

