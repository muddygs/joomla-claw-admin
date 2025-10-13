<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2022 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\FormModel;
use Joomla\Database\ParameterType;
use Joomla\Input\Json;
use ClawCorpLib\Enums\EbPublishedState;
use ClawCorpLib\Lib\Authnetprofile;
use ClawCorpLib\Lib\ClawEvents;
use ClawCorpLib\Lib\EventConfig;
use ClawCorpLib\Lib\Registrant;

require_once JPATH_LIBRARIES . '/claw/External/auth.net/vendor/autoload.php';
require_once JPATH_ROOT . '/../authnet_constants.php';

use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;

class processResult
{
  public bool $success = false;
  public string $successTransactionId = '';
  public string $msg = 'Unknown error';
}

/**
 * Methods to handle a list of records.
 *
 * @since  1.6
 */
class RefundsModel extends FormModel
{
  /**
   * The prefix to use with controller messages.
   *
   * @var    string
   * @since  1.6
   */
  protected $text_prefix = 'COM_CLAW_REFUNDS';

  /**
   * Method to get the record form.
   *
   * @param   array    $data      Data for the form.
   * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
   *
   * @return  Form|boolean  A Form object on success, false on failure
   *
   * @since   1.6
   */
  public function getForm($data = [], $loadData = false)
  {
    // Get the form.
    $form = $this->loadForm('com_claw.refunds', 'refunds', ['control' => 'jform', 'load_data' => $loadData]);

    if (empty($form)) {
      return false;
    }

    return $form;
  }


  /**
   * AJAX Handler
   * 
   * @param Json $json 
   * @return void
   */
  public function refundPopulate(Json $json)
  {
    $invoice = $json->get('jform[invoice]', '', 'string');

    try {
      $uid = Registrant::GetUidFromInvoice($invoice, true);
    } catch (\Exception) {
      echo 'Not found';
      return;
    }

    $statusFields = [
      'Z_AUTHNET_PAYMENTPROFILEID',
      'Z_AUTHNET_PROFILEID',
      'Z_REFUND_AMOUNT',
      'Z_REFUND_DATE',
      'Z_REFUND_TRANSACTION',
    ];

    $eventConfig = new EventConfig('refunds', []);
    $registrant = new Registrant($eventConfig, $uid);
    $registrant->loadCurrentEvents();
    $registrant->mergeFieldValues($statusFields);

    $records = $registrant->records();

    if (count($records) > 0) {
      $nameinfo = reset($records);

      if (false === $nameinfo) {
        echo "<pre>Error getting records:</pre>";
        var_dump($registrant);
        return;
      }

      $f = $nameinfo->registrant->first_name;
      $l = $nameinfo->registrant->last_name;

      echo "<hr><h3>Name on registration: $f $l</h3>";
      Authnetprofile::getCredentials();
      if (ANET_URL == \net\authorize\api\constants\ANetEnvironment::SANDBOX) echo '<h2>SANDBOX</h2>';
    }

    $colName = "col-6 col-lg-3";
    $colInvoice = "col-5 col-lg-2";
    $colRegDate = "col-4 col-lg-2";
    $colTransId = "col-4 col-lg-2";
    $colAmount = "col-1 col-lg-1 text-right";
    $colStatus = "col-3 col-lg-2";

    echo <<< HTML
  <div class="row row-striped">
  <div class="$colName">Event</div>
  <div class="$colInvoice">Invoice #</div>
  <div class="$colRegDate">Reg Date</div>
  <div class="$colTransId">Trans ID</div>
  <div class="$colAmount">Amount</div>
  <div class="$colStatus">Status</div>
  </div>
  HTML;


    $i = 0;
    $dateYearAgo = Factory::getDate("-365 days")->toSql();

    foreach ($records as $e) {
      if ($e->registrant->register_date < $dateYearAgo) continue;

      $t = $e->fieldValue->Z_REFUND_TRANSACTION;
      $d = $e->fieldValue->Z_REFUND_DATE;
      $a = $e->fieldValue->Z_REFUND_AMOUNT;

      $regCode = $e->registrant->registration_code;

      $info = '';

      if ($t != '') {
        $info = $t;
        if ($d != '') $info .= "<br>$d";
        if ($a != '') $info .= "<br>$a";
        $info = <<< HTML
        <a href="#" class="d-inline-block" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-html="true" title="$info">
          <i class="fa fa-info"></i>
        </a>
  HTML;
      }

      $transaction_id = $e->registrant->transaction_id;
      $profileId = 0;

      if (is_numeric($e->fieldValue->Z_AUTHNET_PAYMENTPROFILEID)) {
        $profileId = $e->fieldValue->Z_AUTHNET_PAYMENTPROFILEID;
      }


      if ($e->registrant->deposit_amount > 0) {
        if ($e->registrant->payment_method == 'os_authnet' && is_numeric($e->registrant->transaction_id))
          $this->formatTransactionRow(
            $i++,
            $e->registrant->id,
            $e->registrant->published,
            $e->event->title,
            $e->registrant->invoice_number,
            $e->registrant->register_date,
            $transaction_id,
            $regCode,
            $profileId,
            $e->registrant->deposit_amount,
            $info
          );

        if ($e->registrant->deposit_payment_method == 'os_authnet' && is_numeric($e->registrant->deposit_payment_transaction_id))
          $this->formatTransactionRow(
            $i++,
            $e->registrant->id,
            $e->registrant->published,
            $e->event->title,
            $e->registrant->invoice_number,
            '0000-00-00 00:00:00',
            $e->registrant->deposit_payment_transaction_id,
            '',
            0,
            $e->registrant->payment_amount,
            ''
          );

        continue;
      }

      if ($e->registrant->payment_method == 'os_authnet' && is_numeric($e->registrant->transaction_id))
        $this->formatTransactionRow(
          index: $i++,
          id: $e->registrant->id,
          state: $e->registrant->published,
          title: $e->event->title,
          invoice_number: $e->registrant->invoice_number,
          register_date: $e->registrant->register_date,
          transaction_id: $transaction_id,
          regCode: $regCode,
          profileId: $profileId,
          amount: $e->registrant->amount,
          info: $info
        );
    }

    echo '<hr>';
  }

  private function formatTransactionRow(
    int $index,
    int $id,
    int $state,
    string $title,
    string $invoice_number,
    string $register_date,
    int $transaction_id,
    string $regCode,
    int $profileId,
    float $amount,
    string $info
  ) {
    $colName = "col-6 col-lg-3";
    $colInvoice = "col-5 col-lg-2";
    $colRegDate = "col-4 col-lg-2";
    $colTransId = "col-4 col-lg-2";
    $colAmount = "col-1 col-lg-1 text-right";
    $colStatus = "col-3 col-lg-2";

    $tid = "id=\"refund$index:{$transaction_id}:$amount\"";

    if ($profileId > 0) {
      $transaction_id .= '<br>';
      $transaction_id .= <<< HTML
      <span class="badge rounded-pill bg-danger">P</span>
      <span id="profile{$index}:{$regCode}:{$profileId}">$profileId</span>
  HTML;
    }

    $backEndLink = "/administrator/index.php?option=com_eventbooking&view=registrant&id=$id";

    $status = '<span class="text-info">Paid</span>';

    if (EbPublishedState::cancelled->value == $state) $status = '<span class="text-danger">Cancelled</span>';

    // Check needed warning
    $checkWarning = '';

    if ($register_date != '0000-00-00 00:00:00') {
      $unixtime = strtotime($register_date);
      $unixnow = strtotime('-180 days');

      if ($unixtime < $unixnow) {
        $checkWarning = '<span class="badge bg-warning ms-1">&gt;180</span>';
        $tid = '';
      }
    } else {
      $checkWarning = '<span class="badge bg-info ms-1">???</span>';
    }

    $amount = number_format(round($amount, 2), 2);

    echo <<< HTML
    <div class="row row-striped align-items-center">
    <div class="$colName">$title</div>
    <div class="$colInvoice"><a href="$backEndLink" target="_blank">$invoice_number</a></div>
    <div class="$colRegDate">$register_date$checkWarning</div>
    <div class="$colTransId" $tid>$transaction_id</div>
    <div class="$colAmount">$amount$info</div>
    <div class="$colStatus">$status</div>
    </div>
  HTML;
  }

  function refundProcessRefund(Json $json): void
  {
    $transactionId = $json->get('jform[refundSelect]', 0, 'uint');
    $refundAmount = $json->get('jform[refundAmount]', 0, 'float');
    $cancelAll = $json->get('jform[refundCancelAll]', 0, 'bool');

    $earlyValidation = new processResult();
    $finalResult = new processResult();

    $earlyValidation->success = true;

    if (!$transactionId) {
      $earlyValidation->success = false;
      $earlyValidation->msg .= 'Invalid transaction id requested. ';
    }

    if (!$refundAmount) {
      $earlyValidation->success = false;
      $earlyValidation->msg .= 'Invalid refund amount requested. ';
    }

    if ($earlyValidation->success) {
      $db = $this->getDatabase();

      // Determine event associated with this transactionId, if multiple:
      // 1. If main event, use that
      // 2. Otherwise, pick lowest id in eb_registrants
      $registrantId = 0;
      $email = '';
      $uid = 0;

      $query = $db->getQuery(true);
      $query->select(['id,event_id,user_id,transaction_id,email'])
        ->from('#__eb_registrants')
        ->where('transaction_id = :transactionId', 'OR')
        ->where('deposit_payment_transaction_id = :transactionIdx')
        ->bind(':transactionId', $transactionId, ParameterType::INTEGER)
        ->bind(':transactionIdx', $transactionId, ParameterType::INTEGER)
        ->order('id');

      $db->setQuery($query);
      $rows = $db->loadObjectList();

      if (is_null($rows)) {
        $earlyValidation->success = false;
        $earlyValidation->msg .= 'Transaction not found: ' . $transactionId;
      } else {
        $registrantId = $rows[0]->id;
        $email = $rows[0]->email;
        $uid = $rows[0]->user_id;

        $eventConfig = new EventConfig('refunds');
        $mainEventIds = $eventConfig->getMainEventIds();

        foreach ($rows as $r) {
          if (in_array($r->event_id, $mainEventIds)) {
            $registrantId = $r->id;
            $email = $r->email;
            break;
          }
        }
      }

      $finalResult->msg = 'Unknown error';
    }

    echo '<pre style="color:black;">';

    if ($earlyValidation->success == false) {
      $finalResult->msg = $earlyValidation->msg;
    } else {
      echo "Registrant Database ID: $registrantId\n";
      echo "Email: $email\n\n";

      $finalResult = $this->refundAuthNet($transactionId, $refundAmount, $registrantId, $email);

      if ($finalResult->success) {
        $cancelAllThese = $cancelAll ? $transactionId : '';
        $this->refundToRegistrationRecord($uid, $registrantId, $finalResult->successTransactionId, $refundAmount, $cancelAllThese);
      }
    }


    if ($finalResult->success == false) {
      echo '<span style="color:red;">' . $finalResult->msg . '</span>';
    } else {
      echo $finalResult->msg;
    }

    echo '</pre>';
  }

  function refundChargeProfile(Json $json)
  {
    $profileSelect = $json->get('jform[profileSelect]', '', 'string');
    $profileAmount = $json->get('jform[profileAmount]', 0, 'float');
    $profileDescription = $json->get('jform[profileDescription]', 'CLAW Charge', 'string');

    $result = new processResult();

    $rawRegistrantRow = Registrant::loadRegistrantRow($profileSelect, 'registration_code');

    if ($rawRegistrantRow != null) {
      $uid = $rawRegistrantRow->user_id;
      $event_id = $rawRegistrantRow->event_id;

      // Lookup event alias
      $alias = ClawEvents::eventIdtoAlias($event_id);
      $eventConfig = new EventConfig($alias, []);

      if ($alias !== false) {
        $registrant = new Registrant($eventConfig, $uid, [$event_id]);
        $registrant->loadCurrentEvents();

        /** @var \ClawCorpLib\Lib\RegistrantRecord */
        $record = $registrant->records(true)[0];

        $profile = new authnetprofile($registrant);

        list($status, $transId, $errorMsg) = $profile->chargeCustomerProfile(
          $record,
          $record->fieldValue->Z_AUTHNET_PROFILEID,
          $record->fieldValue->Z_AUTHNET_PAYMENTPROFILEID,
          $profileAmount,
          $profileDescription
        );

        $result->success = $status;
        $result->msg = $errorMsg;

        // Update registration record by adding transaction details
        if ($result->success) {
          $note = date('Y-m-d') . ': ' . implode(',', [$transId != '' ? $transId : 'error', number_format($profileAmount, 2, '.', ''), $profileDescription]);

          $mergeValues = [
            'INTERNAL_NOTES' => implode(PHP_EOL, [$record->fieldValue->INTERNAL_NOTES, $note])
          ];

          $registrant->updateFieldValues($record->registrant->id, $mergeValues);
        }
      }
    }

    echo '<pre style="color:black;">';

    if ($result->success == false) {
      echo '<span style="color:red;">' . $result->msg . '</span>';
    } else {
      echo $result->msg;
    }

    echo '</pre>';
  }

  private function refundAuthNet(string $transactionId, float $refundAmount, string $registrantId, string $email): processResult
  {
    $result = new processResult();

    // For debugging, get clobbered on success
    $result->msg  = "REFUND INPUTS:\n";
    $result->msg .= "Transaction ID: $transactionId\n";
    $result->msg .= " Refund Amount: $refundAmount\n";
    $result->msg .= " Registrant ID: $registrantId\n";
    $result->msg .= "         Email: $email\n\n";

    $response = $this->getTransactionDetails($transactionId);

    if (($response != null) && ($response->getMessages()->getResultCode() == "Ok")) {
      $result->msg  = 'ORIGINAL TRANSACTION:' . "\n";
      $result->msg .= 'Transaction Status: ' . $response->getTransaction()->getTransactionStatus() . "\n";
      $result->msg .= '       Auth Amount: $' . number_format($response->getTransaction()->getAuthAmount(), 2) . "\n";
      $result->msg .= '          Trans ID: ' . $response->getTransaction()->getTransId() . "\n";
      $result->msg .= '       Card Number: ' . $response->getTransaction()->getPayment()->getCreditCard()->getCardNumber() . "\n";
      $cardNumber = $response->getTransaction()->getPayment()->getCreditCard()->getCardNumber();
      $cardNumber = substr($cardNumber, -4);
    } else {
      $result->msg .= "ERROR: Invalid response\n";
      $errorMessages = $response->getMessages()->getMessage();
      $result->msg .= "Response: " . $errorMessages[0]->getCode() . "  " . $errorMessages[0]->getText() . "\n";
      $result->msg .= 'Code: ' . $errorMessages[0]->getCode();
      return $result;
    }

    $response = $this->refundTransaction($cardNumber, $transactionId, $registrantId, $refundAmount, $email);

    if ($response != null) {
      if ($response->getMessages()->getResultCode() == "Ok") {
        $tresponse = $response->getTransactionResponse();

        if ($tresponse != null && $tresponse->getMessages() != null) {
          $transaction = $tresponse->getTransId();
          $result->successTransactionId = $transaction;

          $result->msg .= "\nREFUND TRANSACTION:\n";
          $result->msg .= "Transaction ID: " . $transaction . "\n";
          $result->msg .= "        Amount: $" . number_format($refundAmount, 2, '.', '') . "\n";

          $result->success = true;
          return $result;
        } else {
          $result->msg .= "Transaction Failed (1)\n";
          if ($tresponse->getErrors() != null) {
            $result->msg .= " Error code  : " . $tresponse->getErrors()[0]->getErrorCode() . "\n";
            $result->msg .= " Error message : " . $tresponse->getErrors()[0]->getErrorText() . "\n";
          }

          return $result;
        }
      } else {
        $result->msg .= "\nTransaction Failed (2)\n";
        $tresponse = $response->getTransactionResponse();
        if ($tresponse != null && $tresponse->getErrors() != null) {
          $result->msg .= " Error code  : " . $tresponse->getErrors()[0]->getErrorCode() . "\n";
          $result->msg .= " Error message : " . $tresponse->getErrors()[0]->getErrorText() . "\n";
        } else {
          $result->msg .= " Error code  : " . $response->getMessages()->getMessage()[0]->getCode() . "\n";
          $result->msg .= " Error message : " . $response->getMessages()->getMessage()[0]->getText() . "\n";
        }

        return $result;
      }
    } else {
      $result->msg .= "\nno_response\n";
      return $result;
    }

    $result->msg .= 'UNHANDLED ERROR/SUCCESS STATE. PLEASE FIX. MANUAL EDITS MAY BE NECESSARY.';
    return $result;
  }

  private function getTransactionDetails($transactionId)
  {
    list($merchantId, $transactionKey) = Authnetprofile::getCredentials();

    /* Create a merchantAuthenticationType object with authentication details
        retrieved from the constants file */
    $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
    $merchantAuthentication->setName($merchantId);
    $merchantAuthentication->setTransactionKey($transactionKey);

    $request = new AnetAPI\GetTransactionDetailsRequest();
    $request->setMerchantAuthentication($merchantAuthentication);
    $request->setTransId($transactionId);

    $controller = new AnetController\GetTransactionDetailsController($request);

    /** @var $response net\authorize\api\contract\v1\GetTransactionDetailsResponse */
    $response = $controller->executeWithApiResponse(ANET_URL);

    return $response;
  }

  private function refundTransaction($cardNumber, $refTransId, $registrantId, $amount, $email): \net\authorize\api\contract\v1\CreateTransactionResponse
  {
    list($merchantId, $transactionKey) = Authnetprofile::getCredentials();

    /* Create a merchantAuthenticationType object with authentication details
         retrieved from the constants file */
    $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
    $merchantAuthentication->setName($merchantId);
    $merchantAuthentication->setTransactionKey($transactionKey);

    // Set the transaction's refId
    $refId = 'rid' . $registrantId;

    // Create the payment data for a credit card
    $creditCard = new AnetAPI\CreditCardType();
    $creditCard->setCardNumber((string)$cardNumber);
    $creditCard->setExpirationDate("XXXX");
    $paymentOne = new AnetAPI\PaymentType();
    $paymentOne->setCreditCard($creditCard);
    //create a transaction
    $transactionRequest = new AnetAPI\TransactionRequestType();
    $transactionRequest->setTransactionType("refundTransaction");
    $transactionRequest->setAmount($amount);
    $transactionRequest->setPayment($paymentOne);
    $transactionRequest->setRefTransId($refTransId);

    $customer = new AnetAPI\CustomerDataType();
    $customer->setEmail($email);
    $customer->setType('individual');
    $transactionRequest->setCustomer($customer);

    $order = new AnetAPI\OrderType();
    $order->setDescription("CLAW Refund");
    $transactionRequest->setOrder($order);

    $request = new AnetAPI\CreateTransactionRequest();
    $request->setMerchantAuthentication($merchantAuthentication);
    $request->setRefId($refId);
    $request->setTransactionRequest($transactionRequest);
    $controller = new AnetController\CreateTransactionController($request);

    $response = $controller->executeWithApiResponse(ANET_URL);

    return $response;
  }

  private function refundToRegistrationRecord(int $uid, int $registrantId, string $transaction, float $refundAmount, string $cancelAllThese = ''): void
  {
    $db = $this->getDatabase();

    $date = date('Y-m-d');
    $amount = number_format($refundAmount, 2, '.', '');

    $eventConfig = new EventConfig('refunds', []);
    $registrant = new Registrant($eventConfig, $uid);
    $registrant->loadCurrentEvents();

    $mergeValues = [
      'Z_REFUND_TRANSACTION' => $transaction,
      'Z_REFUND_DATE' => $date,
      'Z_REFUND_AMOUNT' => $amount
    ];

    $registrant->updateFieldValues($registrantId, $mergeValues, true);

    if ('' != $cancelAllThese) {
      $query = 'UPDATE `#__eb_registrants` SET `published`=' . EbPublishedState::cancelled->value . ' WHERE `transaction_id` = ' . $db->quote($cancelAllThese);
      $db->setQuery($query);
      $db->execute();
    }
  }
}
