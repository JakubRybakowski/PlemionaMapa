create table plemiona.colors
(
    id      int auto_increment
        primary key,
    allie   int not null,
    r       int not null,
    g       int not null,
    b       int not null,
    `group` int not null
);

create table plemiona.data_pl140
(
    id        int auto_increment
        primary key,
    timestamp int null
);