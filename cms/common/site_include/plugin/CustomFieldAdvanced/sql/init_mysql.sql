create table EntryAttribute(
	entry_id integer NOT NULL,
	entry_field_id VARCHAR(255) NOT NULL,
	entry_value TEXT,
	entry_extra_values TEXT,
	unique(entry_id, entry_field_id)
) ENGINE=InnoDB;

create index EntryAttribute_entry_id on EntryAttribute(entry_id);
create index EntryAttribute_entry_field_id on EntryAttribute(entry_field_id);
