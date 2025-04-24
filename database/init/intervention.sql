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

