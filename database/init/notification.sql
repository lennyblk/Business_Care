create table notification
(
    id                int auto_increment
        primary key,
    recipient_id      int                                                          not null,
    recipient_type    enum ('Company', 'Employee', 'Provider')                     not null,
    title             varchar(255)                                                 not null,
    message           text                                                         not null,
    creation_date     datetime                         default current_timestamp() null,
    send_date         datetime                                                     null,
    status            enum ('Pending', 'Sent', 'Read') default 'Pending'           null,
    notification_type enum ('Email', 'Push', 'Internal')                           not null
);

