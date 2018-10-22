(function($){
  //Initial recipient system processing
  CRM.api3('CustomField', 'getsingle', {
    "return": ["id"],
    "name": "Email_Recipient_System"
  }).done(function(recipientSystemField) {
    //Page load
    recipient_system_group(recipientSystemField.id);
    //When the salutation type changes
    CRM.$("select[id*='custom_" + recipientSystemField.id + "']").change(function() {
      recipient_system_group(recipientSystemField.id);
    });
  });

})(CRM.$);

function recipient_system_group (fieldId){
  //Process the selected option
  CRM.api3('CustomField', 'getsingle', {
    "return": ["id"],
    "name": 'Recipient_Group'
  }).done(function(recipientGroupField) {
    if (CRM.$("select[id*='custom_" + fieldId + "'] option:selected").val() == 'Group') {
      //Show Group Field field
      CRM.$("tr[class*='custom_" + recipientGroupField.id + "']").show();
      recipient_single_hide_group(true);
    }
    if (CRM.$("select[id*='custom_" + fieldId + "'] option:selected").val() != 'Group') {
      CRM.$("tr[class*='custom_" + recipientGroupField.id + "']").hide();
    }
    if (CRM.$("select[id*='custom_" + fieldId + "'] option:selected").val() == 'Single') {
      recipient_single_hide_group(false);
    }
  });
}

function recipient_single_hide_group(hide){
  CRM.api3('CustomField', 'get', {
    "return": ["id"],
    "name": {"IN":["Recipient_Name","Recipient_Email"]}
  }).done(function(recipientSingle) {
    if (hide) {
      CRM.$.each(recipientSingle.values, function(id,fieldId) {
        CRM.$("tr[class*='custom_" + id + "']").hide();
      });
    } else {
      CRM.$.each(recipientSingle.values, function(id, fieldId) {
        CRM.$("tr[class*='custom_" + id + "']").show();
      });
    }
  });
}
