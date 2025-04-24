create table medical_appointment
(
    id               int auto_increment
        primary key,
    employee_id      int                  not null,
    provider_id      int                  not null,
    appointment_date datetime             not null,
    reason           text                 not null,
    confidential     tinyint(1) default 1 not null,
    constraint medical_appointment_ibfk_1
        foreign key (employee_id) references employee (id),
    constraint medical_appointment_ibfk_2
        foreign key (provider_id) references provider (id)
);

create index employee_id
    on medical_appointment (employee_id);

create index provider_id
    on medical_appointment (provider_id);

