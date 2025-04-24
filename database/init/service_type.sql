create table service_type
(
    id          int auto_increment
        primary key,
    provider_id int            not null,
    title       varchar(255)   not null,
    description text           not null,
    price       decimal(10, 2) not null,
    duration    int            not null,
    constraint service_type_ibfk_1
        foreign key (provider_id) references provider (id)
);

create index provider_id
    on service_type (provider_id);

