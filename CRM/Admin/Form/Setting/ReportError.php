<?php
/*
 +--------------------------------------------------------------------------+
 | Copyright IT Bliss LLC (c) 2012-2013                                     |
 +--------------------------------------------------------------------------+
 | This program is free software: you can redistribute it and/or modify     |
 | it under the terms of the GNU Affero General Public License as published |
 | by the Free Software Foundation, either version 3 of the License, or     |
 | (at your option) any later version.                                      |
 |                                                                          |
 | This program is distributed in the hope that it will be useful,          |
 | but WITHOUT ANY WARRANTY; without even the implied warranty of           |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            |
 | GNU Affero General Public License for more details.                      |
 |                                                                          |
 | You should have received a copy of the GNU Affero General Public License |
 | along with this program.  If not, see <http://www.gnu.org/licenses/>.    |
 +--------------------------------------------------------------------------+
*/

class CRM_Admin_Form_Setting_ReportError extends CRM_Admin_Form_Setting {
  protected $_values;
  protected $_oauth_ok;
  protected $_scheduledJob;

  function preProcess() {
    // Needs to be here as from is build before default values are set
    $this->_values = CRM_Core_BAO_Setting::getItem(REPORTERROR_SETTINGS_GROUP);
  }

  /**
   * Function to build the form
   *
   * @return None
   * @access public
   */
  public function buildQuickForm() {
    $this->applyFilter('__ALL__', 'trim');
    $element =& $this->add('text',
      'mailto',
      ts('Error Report Recipient'),
      CRM_Utils_Array::value('mailto', $this->_values),
      true);

    $results = civicrm_api('ContributionPage', 'get', array('version' => 3, 'is_active' => 1));
    $contribution_pages = array();
    if($results['is_error'] == 0) {
      foreach ($results['values'] as $val) {
        $contribution_pages[$val['id']] = $val['title'];
      }
    }

    $contribution_pages = array_merge(array(0 => ts('-Select-')), $contribution_pages);
    // The <br /> is because we do not know how else to do that!
    $radio_choices = array(
      '0' => ts('Do nothing (show the CiviCRM error)', array('domain' => 'ca.bidon.reporterror')),
      '1' => ts('Redirect to front page of CMS (recommended to avoid confusion to users)', array('domain' => 'ca.bidon.reporterror')),
      '2' => ts('Redirect to a specific contribution page', array('domain' => 'ca.bidon.reporterror'))
    );

    $element = $this->addRadio('noreferer_handle',
      ts('Enable transparent redirection?', array('domain' => 'ca.bidon.reporterror')),
      $radio_choices,
      array('options_per_line' => 1),
      '<br />'
     );

    $element = $this->addYesNo('noreferer_sendreport',
      ts('Send error reports for this particular error?', array('domain' => 'ca.bidon.reporterror'))
    );

    $element = $this->add('select',
      'noreferer_pageid',
      ts('Redirect to Contribution Page', array('domain' => 'ca.bidon.reporterror')),
      $contribution_pages,
      true);

    $this->addRule('mailto', ts('Please enter a valid email address.',
      array('domain' => 'ca.bidon.reporterror')), 'email');

    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => ts('Save'),
        'isDefault' => TRUE,
      ),
      array(
        'type' => 'cancel',
        'name' => ts('Cancel'),
      ),
    ));
  }

  function setDefaultValues() {
    $defaults = $this->_values;
    return $defaults;
  }

  /**
   * Function to validate the form
   *
   * @access public
   * @return None
   */

  /**
   * Function to process the form
   *
   * @access public
   * @return None
   */
  public function postProcess() {
    // store the submitted values in an array
    $params = $this->exportValues();

    $fields = array('noreferer_handle', 'noreferer_pageid', 'noreferer_sendreport', 'mailto');

    foreach ($fields as $field) {
    	$value = $params[$field];
    	$result = CRM_Core_BAO_Setting::setItem($value, REPORTERROR_SETTINGS_GROUP, $field);
    }  

    // we will return to this form by default
    CRM_Core_Session::setStatus(ts('Settings saved.', array('domain' => 'ca.bidon.reporterror')));

  } //end of function

} // end class
