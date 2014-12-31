UPDATE person_contact_methods SET main=false WHERE id in
(select a.id from person_contact_methods a 
join person_contact_methods b on (a.type=b.type and a.person_id=b.person_id and a.id<b.id and a.main and b.main)
);