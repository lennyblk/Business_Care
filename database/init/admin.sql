create table admin
(
    id       int auto_increment
        primary key,
    email    varchar(255)                not null,
    password varchar(255)                not null,
    name     varchar(100)                not null,
    type     varchar(10) default 'admin' not null,
    constraint email
        unique (email)
);

