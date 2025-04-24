create table invoice
(
    id             int auto_increment
        primary key,
    company_id     int                                                   not null,
    contract_id    int                                                   null,
    issue_date     date                                                  not null,
    due_date       date                                                  not null,
    total_amount   decimal(10, 2)                                        not null,
    payment_status enum ('Pending', 'Paid', 'Overdue') default 'Pending' null,
    pdf_path       varchar(255)                                          null,
    details        text                                                  not null,
    constraint invoice_ibfk_1
        foreign key (company_id) references company (id),
    constraint invoice_ibfk_2
        foreign key (contract_id) references contract (id)
);

