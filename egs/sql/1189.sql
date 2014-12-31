begin;
drop view activitiesoverview;
create view activitiesoverview as SELECT activities.id, activities.type_id, activities."owner", activities.company_id, 
activities.person_id, activities.opportunity_id, activities.name, activities.description, activities.startdate, 
activities.enddate, activities.completed, activities.usercompanyid, activities.duration, activities.created, 
activities.alteredby, activities.lastupdated, activities.campaign_id, activities.assigned, activitytype.name AS 
"type", 
opportunities.name AS opportunity, campaigns.name AS campaign, company.name AS company, (person.firstname::text || ' '::text) || person.surname::text AS person
   FROM activities
   LEFT JOIN activitytype ON activities.type_id = activitytype.id
   LEFT JOIN opportunities ON activities.opportunity_id = opportunities.id
   LEFT JOIN campaigns ON activities.campaign_id = campaigns.id
   LEFT JOIN company ON activities.company_id = company.id
   LEFT JOIN person ON activities.person_id = person.id;
commit;
