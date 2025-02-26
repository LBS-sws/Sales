alter table sal_push_message add column key_id int unsigned default 0 after response;
create index idx_push_message_01 on sal_push_message (key_id);