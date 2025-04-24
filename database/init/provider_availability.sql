create table provider_availability
(
    id             int auto_increment
        primary key,
    provider_id    int                                                            not null,
    date_available date                                                           not null,
    start_time     time                                                           not null,
    end_time       time                                                           not null,
    status         enum ('Available', 'Reserved', 'Canceled') default 'Available' null,
    constraint provider_availability_ibfk_1
        foreign key (provider_id) references provider (id)
);

