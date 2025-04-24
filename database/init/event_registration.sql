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

