<?php
/**
 * @file
 * Group email interface.
 */

/**
 * An interface to send a single email.
 *
 * @extends CRM_Petitionemail_Interface
 */
class CRM_Petitionemail_Interface_Group extends CRM_Petitionemail_Interface {

  /**
   * Instantiate the delivery interface.
   *
   * @param int $surveyId
   *   The ID of the petition.
   */
  public function __construct($surveyId) {
    parent::__construct($surveyId);

    $this->neededFields[] = 'Support_Subject';
    $this->neededFields[] = 'Recipient_Group';

    $fields = $this->findFields();
    $petitionemailval = $this->getFieldsData($surveyId);

    foreach ($this->neededFields as $neededField) {
      if (empty($fields[$neededField]) || empty($petitionemailval[$fields[$neededField]])) {
        // TODO: provide something more meaningful.
        return;
      }
    }
    // If all needed fields are found, the system is no longer incomplete.
    $this->isIncomplete = FALSE;
  }

  /**
   * Take the signature form and send an email to the recipient.
   *
   * @param CRM_Campaign_Form_Petition_Signature $form
   *   The petition form.
   */
  public function processSignature($form) {
    // Get the message.
    $messageField = $this->findMessageField();
    if ($messageField === FALSE) {
      return;
    }
    $message = empty($form->_submitValues[$messageField]) ? $this->petitionEmailVal[$this->fields['Support_Message']] : $form->_submitValues[$messageField];
    // If message is left empty and no default message, don't send anything.
    if (empty($message)) {
      return;
    }
    $groupIdField = $this->fields['Recipient_Group'];
    $groupIds = $this->petitionEmailVal[$groupIdField];
    foreach ($groupIds as $groupId) {
      $groupContacts = civicrm_api3('GroupContact', 'get', ['sequential' => 1,'return' => ["contact_id"],'group_id' => "$groupId",'options' => ['limit' => 999999],]);

      foreach ($groupContacts['values'] as $groupContact) {
        $contact = civicrm_api3('Contact', 'getsingle', ['return' => ["display_name", "email"],'id' => $groupContact['contact_id'],]);
        // Setup email message:
        $mailParams = array(
          'groupName' => 'Activity Email Sender',
          'from' => $this->getSenderLine($form->_contactId),
          'toName' => $contact['display_name'],
          'toEmail' => $contact['email'],
          'subject' => $this->petitionEmailVal[$this->fields['Support_Subject']],
          'text' => $message,
        );

        if (!CRM_Utils_Mail::send($mailParams)) {
          CRM_Core_Session::setStatus(ts('Error sending message to %1', array('domain' => 'com.aghstrategies.petitionemail', 1 => $mailParams['toName'])));
        }
        else {
          CRM_Core_Session::setStatus(ts('Message sent successfully to %1', array('domain' => 'com.aghstrategies.petitionemail', 1 => $mailParams['toName'])));
        }

        parent::processSignature($form);
      }
    }
  }

  /**
   * Prepare the signature form with the default message.
   *
   * @param CRM_Campaign_Form_Petition_Signature $form
   *   The petition form.
   */
  public function buildSigForm($form) {
    $defaults = $form->getVar('_defaults');

    $messageField = $this->findMessageField();
    if ($messageField === FALSE) {
      return;
    }
    if (empty($this->petitionEmailVal[$this->fields['Support_Message']])) {
      return;
    }
    else {
      $defaultMessage = $this->petitionEmailVal[$this->fields['Support_Message']];
    }

    foreach ($form->_elements as $element) {
      if ($element->_attributes['name'] == $messageField) {
        $element->_value = $defaultMessage;
      }
    }
    $defaults[$messageField] = $form->_defaultValues[$messageField] = $defaultMessage;
    $form->setVar('_defaults', $defaults);
  }

}
