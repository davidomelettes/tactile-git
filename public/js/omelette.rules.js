$(function() {
	
	// Datepicker
	var datepickerDefaults = {dateFormat: 'd/m/yy'};
	switch (Tactile.DATE_FORMAT) {
		case 'm/d/Y':
			datepickerDefaults.dateFormat = 'm/d/yy';
			break;
	}
	$.datepicker.setDefaults(datepickerDefaults);
	$("input.datefield").datepicker();

	// Flash
	$("#flash").live('click', function(ev) {Om.Flash.clear();});
	setTimeout(function(){Om.Flash.clearMessages();}, 10000);
	
	// Foldables
	$("div.foldable").foldable();
	$("div.ajax-foldable").ajaxFoldable();
	
	// Searches
	$('#header_search').autocomplete({lock:false, subtleText:'Search Everything...', resultsClass:'', reposition:false, autoHideResults:false, resultsContainer:$('#header_search').parent()});
	$('input#organisation_parent, input#person_organisation, input#opportunity_organisation, input#activity_organisation, input#email_organisation').autocomplete({url:'/organisations/filtered_list'});
	$('input#opportunity_person, input#activity_person, input#email_person').autocomplete({url:'/people/filtered_list'});
	$('input#person_person_reports_to').autocomplete({url:'/people/filtered_list'});
	$('input#activity_opportunity, input#email_opportunity').autocomplete({url:'/opportunities/filtered_list'});
	$('div.edit_holder input#organisation_name').dedupe('/organisations/all_companies');
	$('div.edit_holder input#person_surname').dedupe('/people/filtered_list', $('input#person_firstname, input#person_surname'));
	
	// Modals
	$("a#welcome_add_organisation").orgAdder();
	$("a#assign_add_organisation").orgAdder();
	$('a#welcome_add_person').personAdder();
	$('a#assign_add_person').personAdder();
	$("div#related_people a.add_related").personAdder();
	$('a#assign_add_opportunity').oppAdder();
	$("div#related_opportunities a.add_related").oppAdder();
	$('a.opportunity_adder').oppAdder();
	$("div#opportunity_contacts a.add_related").relContactAdder();
	$("div#related_activities a.add_related").actAdder();
	$("div#related_files a.add_related").fileAdder();
	$("div#organisation_contact_methods a.add_related").contactAdder();
	$("div#person_contact_methods a.add_related").contactAdder();
	$('#organisation_addresses a.add_related').addressAdder();
	$('#person_addresses a.add_related').addressAdder();
	$("a#custom_fields_update").customAdder();
	
	// Confirmations
	$('form.delete_form').submit(function(ev){
		if (!confirm("Are you sure you want to delete this?")) {
			ev.preventDefault();
		}
	});
	$('button.delete_link, a.u_sure').live('click', function(ev){
		return confirm("Are you sure you want to delete this?");
	});
	
	// Index Tables
	$('table.index_table').indexTable();
	
	// Timelines
	$(".timeline").timeline();
	
	// Taggers
	$("div#organisations_tags, div#people_tags, div#opportunities_tags, div#activities_tags").tagger();
	
	// Config tables
	$('table.group_values').configTable();
	
	// Custom Value tables
	$('table.custom_values').customvalueTable();
	
	// Expanding textareas
	$('textarea').expandingTextarea();
	
	// Email
	$('a.email, li.email a').sendEmail();
	$('#add_shared_email_address').emailAddressAdder('Add Shared Email Address');
	$('#add_user_email_address').emailAddressAdder('Verify Your Email Address');
	$('#new_email_template').emailTemplateEditor();
	$('#pref_email_templates a.edit').emailTemplateEditor();
	
});
