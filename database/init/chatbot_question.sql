create table chatbot_question
(
    id            int auto_increment
        primary key,
    employee_id   int                                                         not null,
    question      text                                                        not null,
    response      text                                                        null,
    question_date datetime                        default current_timestamp() null,
    status        enum ('Resolved', 'Unresolved') default 'Unresolved'        null,
    constraint chatbot_question_ibfk_1
        foreign key (employee_id) references employee (id)
);

