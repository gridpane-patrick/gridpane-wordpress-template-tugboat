<?php class MyFatoorah extends MyFatoorahHelper{protected $config=[];protected $apiURL='';public function __construct($config){$mfCountries=self::getMFCountries();$this->setApiKey($config);$this->setIsTest($config);$this->setCountryCode($config);self::$loggerObj=$this->config['loggerObj']=empty($config['loggerObj'])?null:$config['loggerObj'];self::$loggerFunc=$this->config['loggerFunc']=empty($config['loggerFunc'])?null:$config['loggerFunc'];$code=$this->config['countryCode'];$this->apiURL=$this->config['isTest']?$mfCountries[$code]['testv2']:$mfCountries[$code]['v2'];}protected function setApiKey($config){if(empty($config['apiKey'])){throw new Exception('Config array must have the "apiKey" key.');}$config['apiKey']=trim($config['apiKey']);if(empty($config['apiKey'])){throw new Exception('The "apiKey" key is required and must be a string.');}$this->config['apiKey']=$config['apiKey'];}protected function setIsTest($config){if(!isset($config['isTest'])){throw new Exception('Config array must have the "isTest" key.');}if(!is_bool($config['isTest'])){throw new Exception('The "isTest" key must be boolean.');}$this->config['isTest']=$config['isTest'];}protected function setCountryCode($config){if(empty($config['countryCode'])){throw new Exception('Config array must have the "countryCode" key.');}$mfCountries=self::getMFCountries();$countriesCodes=array_keys($mfCountries);$config['countryCode']=strtoupper($config['countryCode']);if(!in_array($config['countryCode'],$countriesCodes)){throw new Exception('The "countryCode" key must be one of ('.implode(', ',$countriesCodes).').');}$this->config['countryCode']=$config['countryCode'];}public function callAPI($url,$postFields=null,$orderId=null,$function=null){ini_set('precision',14);ini_set('serialize_precision',-1);$request=isset($postFields)?'POST':'GET';$fields=json_encode($postFields);$msgLog="Order #$orderId ----- $function";$this->log("$msgLog - Request: $fields");$curl=curl_init($url);$option=[CURLOPT_CUSTOMREQUEST=>$request,CURLOPT_POSTFIELDS=>$fields,CURLOPT_HTTPHEADER=>['Authorization: Bearer '.$this->config['apiKey'],'Content-Type: application/json'],CURLOPT_RETURNTRANSFER=>true];curl_setopt_array($curl,$option);$res=curl_exec($curl);$err=curl_error($curl);curl_close($curl);if($err){$this->log("$msgLog - cURL Error: $err");throw new Exception($err);}$this->log("$msgLog - Response: $res");$json=json_decode((string) $res);$error=self::getAPIError($json,(string) $res);if($error){$this->log("$msgLog - Error: $error");throw new Exception($error);}return $json;}protected static function getAPIError($json,$res){$isSuccess=$json->IsSuccess ?? false;if($isSuccess){return '';}$hErr=self::getHtmlErrors($res);if($hErr){return $hErr;}if(is_string($json)){return $json;}if(empty($json)){return(!empty($res)?$res:'Kindly review your MyFatoorah admin configuration due to a wrong entry.');}return self::getJsonErrors($json);}protected static function getHtmlErrors($res){$stripHtmlStr=strip_tags($res);if($res!=$stripHtmlStr&&stripos($stripHtmlStr,'apple-developer-merchantid-domain-association')!==false){return trim(preg_replace('/\s+/',' ',$stripHtmlStr));}return '';}protected static function getJsonErrors($json){$errorsVar=isset($json->ValidationErrors)?'ValidationErrors':'FieldsErrors';if(isset($json->$errorsVar)){$blogDatas=array_column($json->$errorsVar,'Error','Name');$mapFun=function($k,$v){return"$k: $v";};$errArr=array_map($mapFun,array_keys($blogDatas),array_values($blogDatas));return implode(', ',$errArr);}if(isset($json->Data->ErrorMessage)){return $json->Data->ErrorMessage;}return empty($json->Message)?'':$json->Message;}public static function log($msg){$loggerObj=self::$loggerObj;$loggerFunc=self::$loggerFunc;if(empty($loggerObj)){return;}if(is_string($loggerObj)){error_log(PHP_EOL.date('d.m.Y h:i:s').' - '.$msg,3,$loggerObj);}elseif(method_exists($loggerObj,$loggerFunc)){$loggerObj->{$loggerFunc}($msg);}}}class MyFatoorahHelper{public static $loggerObj;public static $loggerFunc;public static function getPhone($inputString){$string3=self::convertArabicDigitstoEnglish($inputString);$string4=preg_replace('/[^0-9]/','',$string3);if(strpos($string4,'00')===0){$string4=substr($string4,2);}if(!$string4){return['',''];}$len=strlen($string4);if($len<3||$len>14){throw new Exception('Phone Number lenght must be between 3 to 14 digits');}if(strlen(substr($string4,3))>3){return[substr($string4,0,3),substr($string4,3)];}return['',$string4];}protected static function convertArabicDigitstoEnglish($inputString){$newNumbers=range(0,9);$persianDecimal=['&#1776;','&#1777;','&#1778;','&#1779;','&#1780;','&#1781;','&#1782;','&#1783;','&#1784;','&#1785;'];$arabicDecimal=['&#1632;','&#1633;','&#1634;','&#1635;','&#1636;','&#1637;','&#1638;','&#1639;','&#1640;','&#1641;'];$arabic=['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];$persian=['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];$string0=str_replace($persianDecimal,$newNumbers,$inputString);$string1=str_replace($arabicDecimal,$newNumbers,$string0);$string2=str_replace($arabic,$newNumbers,$string1);return str_replace($persian,$newNumbers,$string2);}public static function getWeightRate($unit){$lUnit=strtolower($unit);$rateUnits=['1'=>['kg','kgs','كج','كلغ','كيلو جرام','كيلو غرام'],'0.001'=>['g','جرام','غرام','جم'],'0.453592'=>['lbs','lb','رطل','باوند'],'0.0283495'=>['oz','اوقية','أوقية'],];foreach($rateUnits as $rate=>$unitArr){if(array_search($lUnit,$unitArr)!==false){return (float) $rate;}}throw new Exception('Weight units must be in kg, g, lbs, or oz. Default is kg');}public static function getDimensionRate($unit){$lUnit=strtolower($unit);$rateUnits=['1'=>['cm','سم'],'100'=>['m','متر','م'],'0.1'=>['mm','مم'],'2.54'=>['in','انش','إنش','بوصه','بوصة'],'91.44'=>['yd','يارده','ياردة'],];foreach($rateUnits as $rate=>$unitArr){if(array_search($lUnit,$unitArr)!==false){return (float) $rate;}}throw new Exception('Dimension units must be in cm, m, mm, in, or yd. Default is cm');}public static function isSignatureValid($dataArray,$secret,$signature,$eventType=0){if($eventType==2){unset($dataArray['GatewayReference']);}uksort($dataArray,'strcasecmp');$mapFun=function($v,$k){return sprintf("%s=%s",$k,$v);};$outputArr=array_map($mapFun,$dataArray,array_keys($dataArray));$output=implode(',',$outputArr);$hash=base64_encode(hash_hmac('sha256',$output,$secret,true));return $signature===$hash;}public static function getMFCountries(){$cachedFile=dirname(__FILE__).'/mf-config.json';if(file_exists($cachedFile)){if((time()-filemtime($cachedFile)>3600)){$countries=self::getMFConfigFileContent($cachedFile);}if(!empty($countries)){return $countries;}$cache=file_get_contents($cachedFile);return($cache)?json_decode($cache,true):[];}else{return self::getMFConfigFileContent($cachedFile);}}protected static function getMFConfigFileContent($cachedFile){$curl=curl_init('https://portal.myfatoorah.com/Files/API/mf-config.json');$option=[CURLOPT_HTTPHEADER=>['Content-Type: application/json'],CURLOPT_RETURNTRANSFER=>true];curl_setopt_array($curl,$option);$response=curl_exec($curl);$http_code=curl_getinfo($curl,CURLINFO_HTTP_CODE);curl_close($curl);if($http_code==200&&is_string($response)){file_put_contents($cachedFile,$response);return json_decode($response,true);}elseif($http_code==403){touch($cachedFile);$fileContent=file_get_contents($cachedFile);if(!empty($fileContent)){return json_decode($fileContent,true);}}return[];}public static function filterInputField($name,$type='GET'){if(isset($GLOBALS["_$type"][$name])){return htmlspecialchars($GLOBALS["_$type"][$name]);}return null;}}class MyFatoorahList extends MyFatoorah{public function getCurrencyRate($currency){$json=$this->getCurrencyRates();foreach($json as $value){if($value->Text==$currency){return $value->Value;}}throw new Exception('The selected currency is not supported by MyFatoorah');}public function getCurrencyRates(){$url="$this->apiURL/v2/GetCurrenciesExchangeList";return (array) $this->callAPI($url,null,null,'Get Currencies Exchange List');}}class MyFatoorahRefund extends MyFatoorah{public function refund($keyId,$amount,$currencyCode=null,$comment=null,$orderId=null,$keyType='PaymentId'){$postFields=['Key'=>$keyId,'KeyType'=>$keyType,'RefundChargeOnCustomer'=>false,'ServiceChargeOnCustomer'=>false,'Amount'=>$amount,'CurrencyIso'=>$currencyCode,'Comment'=>$comment,];return $this->makeRefund($postFields,$orderId);}public function makeRefund($curlData,$orderId=null){$url="$this->apiURL/v2/MakeRefund";$json=$this->callAPI($url,$curlData,$orderId,'Make Refund');return $json->Data;}}class MyFatoorahShipping extends MyFatoorah{public function getShippingCountries(){$url="$this->apiURL/v2/GetCountries";$json=$this->callAPI($url,null,null,'Get Countries');return $json->Data;}public function getShippingCities($method,$countryCode,$searchValue=''){$url=$this->apiURL.'/v2/GetCities'.'?shippingMethod='.$method.'&countryCode='.$countryCode.'&searchValue='.urlencode(substr($searchValue,0,30));$json=$this->callAPI($url,null,null,"Get Cities: $countryCode");return array_map('ucwords',$json->Data->CityNames);}public function calculateShippingCharge($curlData){$url="$this->apiURL/v2/CalculateShippingCharge";$json=$this->callAPI($url,$curlData,null,'Calculate Shipping Charge');return $json->Data;}}class MyFatoorahSupplier extends MyFatoorah{public function getSupplierDashboard($supplierCode){$url=$this->apiURL.'/v2/GetSupplierDashboard?SupplierCode='.$supplierCode;return $this->callAPI($url,null,null,"Get Supplier Documents");}public function isSupplierApproved($supplierCode){$supplier=$this->getSupplierDashboard($supplierCode);return($supplier->IsApproved&&$supplier->IsActive);}}class MyFatoorahPayment extends MyFatoorah{public static $pmCachedFile=__DIR__.'/mf-methods.json';public function initiatePayment($invoiceValue=0,$displayCurrencyIso='',$isCached=false){$postFields=['InvoiceAmount'=>$invoiceValue,'CurrencyIso'=>$displayCurrencyIso,];$json=$this->callAPI("$this->apiURL/v2/InitiatePayment",$postFields,null,'Initiate Payment');$paymentMethods=($json->Data->PaymentMethods)??[];if(!empty($paymentMethods)&&$isCached){file_put_contents(self::$pmCachedFile,json_encode($paymentMethods));}return $paymentMethods;}public function getCachedVendorGateways(){if(file_exists(self::$pmCachedFile)){$cache=file_get_contents(self::$pmCachedFile);return($cache)?json_decode($cache):[];}else{return $this->initiatePayment(0,'',true);}}public function getCachedCheckoutGateways($isAppleRegistered=false){$gateways=$this->getCachedVendorGateways();$cachedCheckoutGateways=['all'=>[],'cards'=>[],'form'=>[],'ap'=>[]];foreach($gateways as $gateway){$cachedCheckoutGateways=$this->addGatewayToCheckoutGateways($gateway,$cachedCheckoutGateways,$isAppleRegistered);}$cachedCheckoutGateways['ap']=$cachedCheckoutGateways['ap'][0]??[];return $cachedCheckoutGateways;}protected function addGatewayToCheckoutGateways($gateway,$checkoutGateways,$isAppleRegistered){if($gateway->PaymentMethodCode=='ap'){if($isAppleRegistered){$checkoutGateways['ap'][]=$gateway;}else{$checkoutGateways['cards'][]=$gateway;}$checkoutGateways['all'][]=$gateway;}else{if($gateway->IsEmbeddedSupported){$checkoutGateways['form'][]=$gateway;$checkoutGateways['all'][]=$gateway;}elseif(!$gateway->IsDirectPayment){$checkoutGateways['cards'][]=$gateway;$checkoutGateways['all'][]=$gateway;}}return $checkoutGateways;}public function getOnePaymentMethod($gateway,$gatewayType='PaymentMethodId',$invoiceValue=0,$displayCurrencyIso=''){$paymentMethods=$this->initiatePayment($invoiceValue,$displayCurrencyIso);$paymentMethod=null;foreach($paymentMethods as $pm){if($pm->$gatewayType==$gateway){$paymentMethod=$pm;break;}}if(!isset($paymentMethod)){throw new Exception('Please contact Account Manager to enable the used payment method in your account');}return $paymentMethod;}public function getInvoiceURL($curlData,$gatewayId=0,$orderId=null,$sessionId=null,$notificationOption='Lnk'){$this->log('------------------------------------------------------------');$curlData['CustomerEmail']=empty($curlData['CustomerEmail'])?null:$curlData['CustomerEmail'];if(!empty($sessionId)){return $this->embeddedPayment($curlData,$sessionId,$orderId);}elseif($gatewayId=='myfatoorah'||empty($gatewayId)){return $this->sendPayment($curlData,$orderId,$notificationOption);}else{return $this->excutePayment($curlData,$gatewayId,$orderId);}}private function excutePayment($curlData,$gatewayId,$orderId=null){$curlData['PaymentMethodId']=$gatewayId;$json=$this->callAPI("$this->apiURL/v2/ExecutePayment",$curlData,$orderId,'Excute Payment');return['invoiceURL'=>$json->Data->PaymentURL,'invoiceId'=>$json->Data->InvoiceId];}private function sendPayment($curlData,$orderId=null,$notificationOption='Lnk'){$curlData['NotificationOption']=$notificationOption;$json=$this->callAPI("$this->apiURL/v2/SendPayment",$curlData,$orderId,'Send Payment');return['invoiceURL'=>$json->Data->InvoiceURL,'invoiceId'=>$json->Data->InvoiceId];}private function embeddedPayment($curlData,$sessionId,$orderId=null){$curlData['SessionId']=$sessionId;$json=$this->callAPI("$this->apiURL/v2/ExecutePayment",$curlData,$orderId,'Embedded Payment');return['invoiceURL'=>$json->Data->PaymentURL,'invoiceId'=>$json->Data->InvoiceId];}public function getEmbeddedSession($userDefinedField='',$orderId=null){$customerIdentifier=['CustomerIdentifier'=>$userDefinedField];$json=$this->callAPI("$this->apiURL/v2/InitiateSession",$customerIdentifier,$orderId,'Initiate Session');return $json->Data;}public function registerApplePayDomain($url){$domainName=['DomainName'=>parse_url($url,PHP_URL_HOST)];return $this->callAPI("$this->apiURL/v2/RegisterApplePayDomain",$domainName,'','Register Apple Pay Domain');}}class MyFatoorahPaymentEmbedded extends MyFatoorahPayment{protected static $checkoutGateways;public function getCheckoutGateways($invoiceValue,$displayCurrencyIso,$isAppleRegistered){if(!empty(self::$checkoutGateways)){return self::$checkoutGateways;}$gateways=$this->initiatePayment($invoiceValue,$displayCurrencyIso);$mfListObj=new MyFatoorahList($this->config);$allRates=$mfListObj->getCurrencyRates();self::$checkoutGateways=['all'=>[],'cards'=>[],'form'=>[],'ap'=>[]];foreach($gateways as $gateway){$gateway->GatewayData=$this->calcGatewayData($gateway->TotalAmount,$gateway->CurrencyIso,$gateway->PaymentCurrencyIso,$allRates);self::$checkoutGateways=$this->addGatewayToCheckoutGateways($gateway,self::$checkoutGateways,$isAppleRegistered);}if($isAppleRegistered){self::$checkoutGateways['ap']=$this->getOneApplePayGateway(self::$checkoutGateways['ap'],$displayCurrencyIso,$allRates);}return self::$checkoutGateways;}protected function calcGatewayData($totalAmount,$currency,$paymentCurrencyIso,$allRates){foreach($allRates as $data){if($data->Text==$currency){$baseCurrencyRate=$data->Value;}if($data->Text==$paymentCurrencyIso){$gatewayCurrencyRate=$data->Value;}}if(isset($baseCurrencyRate)&&isset($gatewayCurrencyRate)){$baseAmount=ceil(((int)($totalAmount*1000))/$baseCurrencyRate/10)/100;$number=ceil(($baseAmount*$gatewayCurrencyRate*100))/100;return['GatewayTotalAmount'=>number_format($number,2,'.',''),'GatewayCurrency'=>$paymentCurrencyIso];}else{return['GatewayTotalAmount'=>$totalAmount,'GatewayCurrency'=>$currency];}}protected function getOneApplePayGateway($apGateways,$displayCurrency,$allRates){$displayCurrencyIndex=array_search($displayCurrency,array_column($apGateways,'PaymentCurrencyIso'));if($displayCurrencyIndex){return $apGateways[$displayCurrencyIndex];}$defCurKey=array_search('1',array_column($allRates,'Value'));$defaultCurrency=$allRates[$defCurKey]->Text;$defaultCurrencyIndex=array_search($defaultCurrency,array_column($apGateways,'PaymentCurrencyIso'));if($defaultCurrencyIndex){return $apGateways[$defaultCurrencyIndex];}if(isset($apGateways[0])){return $apGateways[0];}return[];}}class MyFatoorahPaymentStatus extends MyFatoorahPayment{public function getPaymentStatus($keyId,$KeyType,$orderId=null,$price=null,$currency=null){$curlData=['Key'=>$keyId,'KeyType'=>$KeyType];$json=$this->callAPI("$this->apiURL/v2/GetPaymentStatus",$curlData,$orderId,'Get Payment Status');$data=$json->Data;$msgLog='Order #'.$data->CustomerReference.' ----- Get Payment Status';if(!self::checkOrderInformation($data,$orderId,$price,$currency)){$err='Trying to call data of another order';$this->log("$msgLog - Exception is $err");throw new Exception($err);}if($data->InvoiceStatus=='Paid'||$data->InvoiceStatus=='DuplicatePayment'){$data=self::getSuccessData($data);$this->log("$msgLog - Status is Paid");}elseif($data->InvoiceStatus!='Paid'){$data=self::getErrorData($data,$keyId,$KeyType);$this->log("$msgLog - Status is ".$data->InvoiceStatus.'. Error is '.$data->InvoiceError);}return $data;}private static function checkOrderInformation($data,$orderId=null,$price=null,$currency=null){if($orderId&&$orderId!=$data->CustomerReference){return false;}list($valStr,$mfCurrency)=explode(' ',$data->InvoiceDisplayValue);$mfPrice=floatval(preg_replace('/[^\d.]/','',$valStr));if($price&&$price!=$mfPrice){return false;}return!($currency&&$currency!=$mfCurrency);}private static function getSuccessData($data){foreach($data->InvoiceTransactions as $transaction){if($transaction->TransactionStatus=='Succss'){$data->InvoiceStatus='Paid';$data->InvoiceError='';$data->focusTransaction=$transaction;return $data;}}return $data;}private static function getErrorData($data,$keyId,$KeyType){$focusTransaction=self::{"getLastTransactionOf$KeyType"}($data->InvoiceTransactions,$keyId);if($focusTransaction&&$focusTransaction->TransactionStatus=='Failed'){$data->InvoiceStatus='Failed';$data->InvoiceError=$focusTransaction->Error.'.';$data->focusTransaction=$focusTransaction;return $data;}$ExpiryDateTime=$data->ExpiryDate.' '.$data->ExpiryTime;$ExpiryDate=new \DateTime($ExpiryDateTime,new \DateTimeZone('Asia/Kuwait'));$currentDate=new \DateTime('now',new \DateTimeZone('Asia/Kuwait'));if($ExpiryDate<$currentDate){$data->InvoiceStatus='Expired';$data->InvoiceError='Invoice is expired since '.$data->ExpiryDate.'.';return $data;}$data->InvoiceStatus='Pending';$data->InvoiceError='Pending Payment.';return $data;}private static function getLastTransactionOfPaymentId($transactions,$paymentId){foreach($transactions as $transaction){if($transaction->PaymentId==$paymentId&&$transaction->Error){return $transaction;}}return null;}private static function getLastTransactionOfInvoiceId($transactions){$usortFun=function($a,$b){return strtotime($a->TransactionDate)-strtotime($b->TransactionDate);};usort($transactions,$usortFun);return end($transactions);}}