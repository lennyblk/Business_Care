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

