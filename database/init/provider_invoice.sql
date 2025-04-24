create table provider_invoice
(
    id             int auto_increment
        primary key,
    provider_id    int                                        not null,
    month          int                                        not null,
    year           int                                        not null,
    total_amount   decimal(10, 2)                             not null,
    payment_status enum ('Pending', 'Paid') default 'Pending' null,
    issue_date     date                                       not null,
    payment_date   date                                       null,
    pdf_path       varchar(255)                               null,
    constraint provider_invoice_ibfk_1
        foreign key (provider_id) references provider (id)
);

