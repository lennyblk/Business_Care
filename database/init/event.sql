create table event
(
    id            int auto_increment
        primary key,
    name          varchar(255)                                              not null,
    description   text                                                      null,
    date          datetime                                                  not null,
    event_type    enum ('Webinar', 'Conference', 'Sport Event', 'Workshop') not null,
    capacity      int                                                       not null,
    location      varchar(255)                                              null,
    registrations int default 0                                             not null,
    company_id    int                                                       null,
    constraint fk_event_company
        foreign key (company_id) references company (id)
);

