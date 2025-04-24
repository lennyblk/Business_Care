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

