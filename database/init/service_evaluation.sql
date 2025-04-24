create table service_evaluation
(
    id              int auto_increment
        primary key,
    intervention_id int                                  not null,
    employee_id     int                                  not null,
    rating          decimal(3, 2)                        not null,
    comment         text                                 null,
    evaluation_date datetime default current_timestamp() null,
    constraint service_evaluation_ibfk_1
        foreign key (intervention_id) references intervention (id),
    constraint service_evaluation_ibfk_2
        foreign key (employee_id) references employee (id)
);

