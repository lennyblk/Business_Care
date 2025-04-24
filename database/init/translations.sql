create table translations
(
    id              int auto_increment
        primary key,
    translation_key varchar(255) not null,
    language        varchar(10)  not null,
    text            text         not null,
    constraint unique_translation
        unique (translation_key, language)
);

