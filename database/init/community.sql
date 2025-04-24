create table community
(
    id          int auto_increment
        primary key,
    name        varchar(255)                  not null,
    description text                          not null,
    type        enum ('Internal', 'External') not null
);

