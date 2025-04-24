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

