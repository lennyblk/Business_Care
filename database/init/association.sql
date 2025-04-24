create table association
(
    id           int auto_increment
        primary key,
    name         varchar(255)                                 not null,
    description  text                                         not null,
    domain       varchar(100)                                 not null,
    contact_info varchar(255)                                 not null,
    website      varchar(255)                                 null,
    status       enum ('Active', 'Inactive') default 'Active' null
);

