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

