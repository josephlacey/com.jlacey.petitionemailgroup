(function($){
  // Locate the field controlling the recipient email system.
  CRM.api3('CustomField', 'getsingle', {
    "return": ["id"],
    "name": "Email_Recipient_System"
  }).done(function(recipientSystemField) {
    // Display the right system based on the initial value.
    update_group_recipient_display(recipientSystemField.id);
    //When the recipient system is changed, update the display. 
    CRM.$("select[id*='custom_" + recipientSystemField.id + "']").change(function() {
      update_group_recipient_display(recipientSystemField.id);
    });
  });

})(CRM.$);

function update_group_recipient_display(fieldId){
  //Process the selected option
  CRM.api3('CustomField', 'getsingle', {
    "return": ["id"],
    "name": 'Recipient_Group'
  }).done(function(recipientGroupField) {
    var selectedSystem = CRM.$("select[id*='custom_" + fieldId + "'] option:selected").val();
    if ( selectedSystem == 'Group') {
      // Show Group Field
      CRM.$("tr[class*='custom_" + recipientGroupField.id + "']").show();
    }
    else {
      CRM.$("tr[class*='custom_" + recipientGroupField.id + "']").hide();
    }
  });
}
