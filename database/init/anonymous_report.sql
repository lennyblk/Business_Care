create table anonymous_report
(
    id                    int auto_increment
        primary key,
    encrypted_employee_id varchar(255)                                                       not null,
    report_date           datetime                               default current_timestamp() null,
    description           text                                                               not null,
    category              varchar(100)                                                       not null,
    status                enum ('New', 'Processing', 'Resolved') default 'New'               null,
    severity_level        int                                    default 1                   null
);

