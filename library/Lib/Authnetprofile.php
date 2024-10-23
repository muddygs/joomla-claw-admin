<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorpLib\Lib;

defined('_JEXEC') or die('Restricted access');

use ClawCorpLib\Enums\EbPaymentStatus;
use ClawCorpLib\Enums\EbPaymentTypes;
use ClawCorpLib\Enums\EbPublishedState;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

require_once JPATH_LIBRARIES . '/claw/External/auth.net/vendor/autoload.php';
require_once JPATH_ROOT . '/../authnet_constants.php';

use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;

use ClawCorpLib\Lib\Registrant;

class Authnetprofile
{
  public static array $actionLog = [];

  public function __construct(
    private registrant $_r,
    private bool $cron = false
  ) {
    $fields = ['Z_AUTHNET_PROFILEID', 'Z_AUTHNET_PAYMENTPROFILEID', 'INTERNAL_NOTES'];
    $this->_r->mergeFieldValues($fields);
    $this->cron = $cron;

    Authnetprofile::getCredentials();
  }

  public function chargeCustomerProfile(RegistrantRecord $record, string $profileId, string $paymentProfileId, float $amount, string $subject): array
  {
    $amount = number_format($amount, 2, '.', '');

    echo '<pre style="color:black;">';
    list($merchantId, $transactionKey) = Authnetprofile::getCredentials();

    $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
    $merchantAuthentication->setName($merchantId);
    $merchantAuthentication->setTransactionKey($transactionKey);

    // Set the transaction's refId
    $refId = $record->registrant->invoice_number;
    echo "NAME: " . $record->registrant->first_name . ' ' . $record->registrant->last_name . "\n";
    echo "INVOICE: " . $record->registrant->invoice_number . "\n";
    echo "AMOUNT: $" . $amount . "\n";
    echo "USING:         PROFILE ID : " . $profileId . "\n";
    echo "USING: PAYMENT PROFILE ID : " . $paymentProfileId . "\n";

    $profileToCharge = new AnetAPI\CustomerProfilePaymentType();
    $profileToCharge->setCustomerProfileId($profileId);
    $paymentProfile = new AnetAPI\PaymentProfileType();
    $paymentProfile->setPaymentProfileId($paymentProfileId);
    $profileToCharge->setPaymentProfile($paymentProfile);

    $transactionRequestType = new AnetAPI\TransactionRequestType();
    $transactionRequestType->setTransactionType("authCaptureTransaction");
    $transactionRequestType->setAmount($amount);
    $transactionRequestType->setCurrencyCode('USD');
    $transactionRequestType->setProfile($profileToCharge);

    $order = new AnetAPI\OrderType();
    $order->setInvoiceNumber($record->registrant->invoice_number);
    $order->setDescription($subject);
    $transactionRequestType->setOrder($order);

    $request = new AnetAPI\CreateTransactionRequest();
    $request->setMerchantAuthentication($merchantAuthentication);
    $request->setRefId($refId);
    $request->setTransactionRequest($transactionRequestType);
    $controller = new AnetController\CreateTransactionController($request);
    /** @var \net\authorize\api\contract\v1\CreateTransactionResponse */
    $response = $controller->executeWithApiResponse(ANET_URL);

    $status = false;
    $transactionId = '';
    $errorMsg = '';

    if ($response != null) {
      if ($response->getMessages()->getResultCode() == "Ok") {
        $innerResponse = $response->getTransactionResponse();

        if ($innerResponse != null && $innerResponse->getMessages() != null) {
          echo " Transaction Response code : " . $innerResponse->getResponseCode() . "\n";
          echo " Charge Customer Profile APPROVED  :" . "\n";
          echo " Charge Customer Profile AUTH CODE : " . $innerResponse->getAuthCode() . "\n";
          echo " Charge Customer Profile TRANS ID  : " . $innerResponse->getTransId() . "\n";
          echo " Code : " . $innerResponse->getMessages()[0]->getCode() . "\n";
          echo " Description : " . $innerResponse->getMessages()[0]->getDescription() . "\n";
          $status = true;
          $transactionId = $innerResponse->getTransId();
        } else {
          echo "Transaction Failed \n";
          if ($innerResponse->getErrors() != null) {
            echo " Error code  : " . $innerResponse->getErrors()[0]->getErrorCode() . "\n";
            echo " Error message : " . $innerResponse->getErrors()[0]->getErrorText() . "\n";
            $errorMsg = $innerResponse->getErrors()[0]->getErrorText();
          }
        }
      } else {
        echo "Transaction Failed \n";
        $innerResponse = $response->getTransactionResponse();
        if ($innerResponse != null && $innerResponse->getErrors() != null) {
          echo " Error code  : " . $innerResponse->getErrors()[0]->getErrorCode() . "\n";
          echo " Error message : " . $innerResponse->getErrors()[0]->getErrorText() . "\n";
          $errorMsg = $innerResponse->getErrors()[0]->getErrorText();
        } else {
          echo " Error code  : " . $response->getMessages()->getMessage()[0]->getCode() . "\n";
          echo " Error message : " . $response->getMessages()->getMessage()[0]->getText() . "\n";
          $errorMsg = $response->getMessages()->getMessage()[0]->getText();
        }
      }
    } else {
      echo  "No response returned \n";
    }
    echo '</pre>';

    return [$status, $transactionId, $errorMsg];
  }

  public function createProfiles(): int
  {
    $msg = [];
    $count = 0;

    $uid = $this->_r->uid();

    if (0 == $uid) {
      die("Problem with registrant record.");
    }

    foreach ($this->_r->records() as $r) {
      if (
        $r->registrant->published == EbPublishedState::published->value &&
        //$r->registrant->payment_status == registrationPaymentStatus::partial &&
        $r->registrant->payment_method == EbPaymentTypes::authnet->value &&
        is_numeric($r->registrant->transaction_id) &&
        (empty($r->fieldValue->Z_AUTHNET_PAYMENTPROFILEID) || empty($r->fieldValue->Z_AUTHNET_PROFILEID))
      ) {
        list($profileId, $paymentProfileId) = $this->createProfile($r);
        if (0 == $profileId || 0 == $paymentProfileId) {
          echo "ERROR CREATING PROFILE FOR: " . $r->registrant->id . "\n";
          $this->storeProfileId($r->registrant->id, 'error', 'error');
          continue;
        } else {
          $this->storeProfileId($r->registrant->id, $profileId, $paymentProfileId);
          $count++;
        }
      } else {
        continue;

        // echo "NAME: " . $r->registrant->first_name . ' ' . $r->registrant->last_name . "\n";
        // echo "INVOICE: " . $r->registrant->invoice_number . "\n";
        // echo "USING:         PROFILE ID : " . $r->fieldValue->Z_AUTHNET_PROFILEID . "\n";
        // echo "USING: PAYMENT PROFILE ID : " . $r->fieldValue->Z_AUTHNET_PAYMENTPROFILEID . "\n";
      }

      $amountDue = $this->getAmountDue($r->registrant->invoice_number);
      $amountDue = number_format($amountDue, 2, '.', '');
      $msg[] =  'DUE : ' . $amountDue;
    }

    $this->profilelog($msg, 'pre');
    return $count;
  }

  private function createProfile(registrantRecord $transactionRecord): array
  {
    $msg = [];
    $msg[] =  "START   : " . $transactionRecord->registrant->user_id;
    $msg[] =  "-- NAME    : " . $transactionRecord->registrant->first_name . ' ' . $transactionRecord->registrant->last_name;
    $msg[] =  "-- INVOICE : " . $transactionRecord->registrant->invoice_number;

    $customerId = $transactionRecord->registrant->user_id;
    $email = $transactionRecord->registrant->email;
    $transaction = $transactionRecord->registrant->transaction_id;
    $f = $transactionRecord->registrant->first_name;
    $l = $transactionRecord->registrant->last_name;

    list($merchantId, $transactionKey) = Authnetprofile::getCredentials();

    /* Create a merchantAuthenticationType object with authentication details
       retrieved from the constants file */
    $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
    $merchantAuthentication->setName($merchantId);
    $merchantAuthentication->setTransactionKey($transactionKey);

    $customerProfile = new AnetAPI\CustomerProfileBaseType();
    $customerProfile->setMerchantCustomerId($customerId);
    $customerProfile->setEmail($email);
    $customerProfile->setDescription("$f $l");

    $request = new AnetAPI\CreateCustomerProfileFromTransactionRequest();
    $request->setMerchantAuthentication($merchantAuthentication);
    $request->setTransId($transaction);
    $request->setCustomer($customerProfile);

    $controller = new AnetController\CreateCustomerProfileFromTransactionController($request);

    /** @var \net\authorize\api\contract\v1\CreateCustomerProfileResponse */
    $response = $controller->executeWithApiResponse(ANET_URL);

    $profileId = 0;
    $paymentProfileId = 0;

    if (($response != null) && ($response->getMessages()->getResultCode() == "Ok")) {
      $profileId =  $response->getCustomerProfileId();
      $paymentProfileId = $response->getCustomerPaymentProfileIdList()[0];
      $msg[] =  "SUCCESS:         PROFILE ID : " . $profileId;
      $msg[] =  "SUCCESS: PAYMENT PROFILE ID : " . $paymentProfileId;
    } else {
      $msg[] =  "*** ERROR :  Invalid response\n";
      $errorMessages = $response->getMessages()->getMessage();
      $msg[] =  "Response : " . $errorMessages[0]->getCode() . "  " . $errorMessages[0]->getText();
      $msg[] =  "Debug : " . ANET_URL . ' ' . $customerId . " " . $email . " " . $transaction;
    }

    $msg[] =  "END     : " . $transactionRecord->registrant->user_id;
    $this->profilelog($msg);
    return [$profileId, $paymentProfileId];
  }

  public static function getCredentials()
  {
    require_once JPATH_ROOT . '/../authnet_constants.php';

    $uri_path = Uri::getInstance()->getHost();
    if (str_contains($uri_path, 'clawinfo') && !str_contains($uri_path, 'sandbox')) {
      if (!defined('ANET_URL')) define('ANET_URL', \net\authorize\api\constants\ANetEnvironment::PRODUCTION);
      $merchantId = \Constants::MERCHANT_LOGIN_ID;
      $key = \Constants::MERCHANT_TRANSACTION_KEY;
    } else {
      if (!defined('ANET_URL')) define('ANET_URL', \net\authorize\api\constants\ANetEnvironment::SANDBOX);
      $merchantId = \Constants::MERCHANT_LOGIN_ID_SANDBOX;
      $key = \Constants::MERCHANT_TRANSACTION_KEY_SANDBOX;
    }

    return [$merchantId, $key];
  }

  public function getAmountDue(string $invoiceId)
  {
    foreach ($this->_r->records() as $r) {
      if ($r->registrant->invoice_number == $invoiceId) {
        if (
          $r->registrant->payment_status != EbPaymentStatus::paid->value
          && $r->registrant->published != EbPublishedState::cancelled->value
        ) {
          return $r->registrant->amount - $r->registrant->deposit_amount - $r->registrant->discount_amount;
        } else {
          return 0;
        }
      }
    }

    die(__FILE__ . ": Unknown invoice ID: " . $invoiceId);
  }

  public function updateDepositPayment(int $rowId, $amount, $transactionId)
  {
    /** @var \Joomla\Database\DatabaseDriver */
    $db = Factory::getContainer()->get('DatabaseDriver');
    $query = $db->getQuery(true);

    $fields = [
      $db->qn('process_deposit_payment') . '= 1',
      $db->qn('deposit_payment_transaction_id') . '=' . $db->q($transactionId),
      $db->qn('deposit_payment_method') . '=' . $db->q(EbPaymentTypes::authnet->value),
      $db->qn('payment_amount') . '=' . $db->q($amount),
      $db->qn('payment_status') . '=' . $db->q(EbPaymentStatus::paid->value),
    ];

    $conditions = [
      $db->qn('id') . '=' . $db->q($rowId)
    ];

    $query->update($db->qn('#__eb_registrants'))->set($fields)->where($conditions);
    $db->setQuery($query);
    $db->execute();

    // Now set the custom field that tracks the deposit payment date
    // NOTE: EB does not have a field for this, so if someone pays manually, the information
    // will be blank
    // TODO: Check into hook on setting field for manual payments


    return;
  }

  public function storeProfileId(int $rowId, string $profileId, string $paymentProfileId)
  {
    $update = [
      'Z_AUTHNET_PAYMENTPROFILEID' => $paymentProfileId,
      'Z_AUTHNET_PROFILEID' => $profileId
    ];

    $this->_r->updateFieldValues($rowId, $update);
  }
  #region deprecated hotel stuff
  //   public static function getProfileCount(int $max = 0): int
  //   {
  //     $db = Factory::getDbo();
  //     $profileId = ClawEvents::getFieldId('Z_AUTHNET_PROFILEID');
  //     $published = registrationPublishedState::published;
  //     $partial = registrationPaymentStatus::partial;
  //     $authnet = $db->q(paymentTypes::authnet);

  //     $hotelEvents = new clawEvents(CLAWALIASES::hotels[0]);
  //     $hotelEventIds = join(',',$hotelEvents->getEventIds());

  //     $query = <<< SQL
  //     SELECT COUNT(*)
  //     FROM #__eb_registrants r
  //     LEFT OUTER JOIN #__eb_field_values v ON (v.registrant_id = r.id AND v.field_id = $profileId)
  //     WHERE r.published = $published AND 
  //       r.payment_status = $partial AND
  //       r.payment_method = $authnet AND
  //       (v.field_value IS NULL OR v.field_value = "") AND
  //       ( r.amount - r.deposit_amount > 0) AND
  //       r.transaction_id REGEXP '^[0-9]+$' AND
  //       r.event_id IN ($hotelEventIds)
  // SQL;

  //     if ( $max > 0 ) {
  //       $query .= " LIMIT $max";
  //     }

  //     $db->setQuery($query);
  //     $result = $db->loadResult();

  //     return $result;
  //   }

  //   public static function getToChargeCount(): int
  //   {
  //     $db = Factory::getDbo();
  //     $published = registrationPublishedState::published;

  //     $hotelEvents = new clawEvents(CLAWALIASES::hotels[0]);
  //     $hotelEventIds = join(',',$hotelEvents->getEventIds());

  //     $partial = registrationPaymentStatus::partial;
  // 		$authnet = $db->q(paymentTypes::authnet);

  //     $query = <<< SQL
  //     SELECT COUNT(*)
  //     FROM #__eb_registrants
  //       WHERE published = $published AND event_id IN ($hotelEventIds) AND
  //       payment_status = $partial AND
  //       payment_method = $authnet AND
  //       transaction_id REGEXP '^[0-9]+$' AND
  //       ( amount - deposit_amount - discount_amount > 0)
  // SQL;

  //     $db->setQuery($query);
  //     $result = $db->loadResult();

  //     return $result;
  //   }

  //   public static function getToChargeReport( string $orderBy = "invoice_number" ): string
  //   {
  //     $db = Factory::getDbo();
  //     $published = registrationPublishedState::published;

  //     $profileIdField = ClawEvents::getFieldId("Z_AUTHNET_PROFILEID");

  //     $hotelEvents = new clawEvents(CLAWALIASES::hotels[0]);
  //     $hotelEventIds = join(',',$hotelEvents->getEventIds());

  //     $partial = registrationPaymentStatus::partial;
  // 		$authnet = $db->q(paymentTypes::authnet);

  //     //$orderBy = "register_date";

  //     $query = <<< SQL
  //     SELECT r.id,first_name,last_name,invoice_number,transaction_id,register_date,amount - deposit_amount - discount_amount AS due
  //     FROM #__eb_registrants r
  //     LEFT OUTER JOIN #__eb_field_values v ON v.field_id = $profileIdField AND v.registrant_id = r.id
  //       WHERE r.published = $published AND r.event_id IN ($hotelEventIds) AND
  //       r.payment_status = $partial AND
  //       r.payment_method = $authnet AND
  //       r.transaction_id REGEXP '^[0-9]+$' AND
  //       ( amount - deposit_amount - discount_amount > 0) AND
  //       v.field_value IS NULL
  //       ORDER BY r.$orderBy
  // SQL;

  //     $db->setQuery($query);
  //     $rows = $db->loadRowList();

  //     $result = [];
  //     $result[] = '<pre>';

  //     foreach ( $rows AS $row ) {
  //       $result[] = implode(',', $row);
  //     }
  //     $result[] = '</pre>';

  //     return implode("\n", $result);
  //   }
  #endregion


  /**
   * @param string $eventAlias 
   * @param int $maximum_records
   * @param bool $cron 
   * @return array Log of creation actions
   * @throws RuntimeException 
   */
  public static function create(string $eventAlias, int $maximum_records = 10, bool $cron = false): array
  {
    self::$actionLog = [];
    set_time_limit(0);

    // Used for generic registrant access
    $registrants = new Registrants('refunds');
    $eventConfig = new EventConfig($eventAlias, []);

    $count = 0;

    /** @var \ClawCorpLib\Lib\PackageInfo */
    foreach ($eventConfig->packageInfos as $packageInfo) {
      if (!$packageInfo->authNetProfile || $packageInfo->published != EbPublishedState::published) continue;
      $description = '';

      $recordsByEventId = $registrants->byEventId($packageInfo->eventId);

      /** @var \ClawCorpLib\Lib\Registrant $registrant */
      foreach ($recordsByEventId as $registrant) {
        $profile = new Authnetprofile($registrant, $cron);
        if (!$description) {
          $description = $packageInfo->title;
          $profile->profilelog([$description], 'h1');
        }

        $count += $profile->createProfiles();

        if ($count >= $maximum_records) break;
      }

      if ($count >= $maximum_records) break;
    }

    self::$actionLog[] = (sprintf('Profiles Created: %d ', $count));
    return self::$actionLog;
  }

  private function profilelog(array $msg, string $tag = 'pre')
  {
    if (!count($msg)) return;
    $output = implode(PHP_EOL, $msg) . PHP_EOL;

    if (!$this->cron) {
?>
      <<?= $tag ?>>
        <?= $output ?>
      </<?= $tag ?>>
<?php
    } else {
      self::$actionLog[] = $output;
    }
  }

  // These sections are here in case we need to bulk cancel subscriptions again

  // public static function getListOfSubscriptionIds(int $limit = 1000, int $offset = 1)
  // {
  //   list($merchantId, $transactionKey) = Authnetprofile::getCredentials();

  //   /* Create a merchantAuthenticationType object with authentication details
  //      retrieved from the constants file */
  //   $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
  //   $merchantAuthentication->setName($merchantId);
  //   $merchantAuthentication->setTransactionKey($transactionKey);

  //   // Set the transaction's refId
  //   $refId = 'ref' . time();

  //   $sorting = new AnetAPI\ARBGetSubscriptionListSortingType();
  //   $sorting->setOrderBy("id");
  //   $sorting->setOrderDescending(false);

  //   $paging = new AnetAPI\PagingType();
  //   $paging->setLimit($limit);
  //   $paging->setOffset($offset);

  //   $request = new AnetAPI\ARBGetSubscriptionListRequest();
  //   $request->setMerchantAuthentication($merchantAuthentication);
  //   $request->setRefId($refId);
  //   $request->setSearchType("subscriptionActive");
  //   $request->setSorting($sorting);
  //   $request->setPaging($paging);


  //   $controller = new AnetController\ARBGetSubscriptionListController($request);
  //   /** @var $response \net\authorize\api\contract\v1\ARBGetSubscriptionListResponse */
  //   $response = $controller->executeWithApiResponse(ANET_URL);

  //   if (($response != null) && ($response->getMessages()->getResultCode() == "Ok")) {
  //     $subscriptionIds = [];

  //     echo '<pre>';
  //       echo "SUCCESS: Subscription Details retrieved". PHP_EOL;
  //       echo "Total Number In Results:" . $response->getTotalNumInResultSet() . PHP_EOL;
  //     echo '</pre>';
  //     foreach ($response->getSubscriptionDetails() as $subscriptionDetails) {
  //       $subscriptionIds[] = $subscriptionDetails->getId();
  //     }
  //     var_dump($response);
  //   } else {
  //       echo "ERROR :  Invalid response\n";
  //       $errorMessages = $response->getMessages()->getMessage();
  //       echo "Response : " . $errorMessages[0]->getCode() . "  " .$errorMessages[0]->getText() . "\n";
  //       die();
  //   }

  //   return $subscriptionIds;
  // }

  // public static function cancelSubscription($subscriptionId)
  // { 
  //   list($merchantId, $transactionKey) = Authnetprofile::getCredentials();

  //   /* Create a merchantAuthenticationType object with authentication details
  //      retrieved from the constants file */
  //   $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
  //   $merchantAuthentication->setName($merchantId);
  //   $merchantAuthentication->setTransactionKey($transactionKey);

  //   // Set the transaction's refId
  //   $refId = 'ref' . time();

  //   $request = new AnetAPI\ARBCancelSubscriptionRequest();
  //   $request->setMerchantAuthentication($merchantAuthentication);
  //   $request->setRefId($refId);
  //   $request->setSubscriptionId($subscriptionId);

  //   $controller = new AnetController\ARBCancelSubscriptionController($request);

  //   $response = $controller->executeWithApiResponse(ANET_URL);

  //   if (($response != null) && ($response->getMessages()->getResultCode() == "Ok"))
  //   {
  //       $successMessages = $response->getMessages()->getMessage();
  //       echo "SUCCESS : " . $successMessages[0]->getCode() . "  " .$successMessages[0]->getText() . "\n";

  //    }
  //   else
  //   {
  //       echo "ERROR :  Invalid response\n";
  //       $errorMessages = $response->getMessages()->getMessage();
  //       echo "Response : " . $errorMessages[0]->getCode() . "  " .$errorMessages[0]->getText() . "\n";

  //   }

  //   return $response;

  // }


}
