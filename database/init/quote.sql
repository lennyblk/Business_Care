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

