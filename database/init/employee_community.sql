create table employee_community
(
    employee_id  int not null,
    community_id int not null,
    primary key (employee_id, community_id),
    constraint employee_community_ibfk_1
        foreign key (employee_id) references employee (id),
    constraint employee_community_ibfk_2
        foreign key (community_id) references community (id)
);

create index community_id
    on employee_community (community_id);

