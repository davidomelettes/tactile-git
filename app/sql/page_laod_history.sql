begin;
create table page_load_history (page varchar not null, avgtime numeric not null, day date not null, primary key (page, day));
commit;
