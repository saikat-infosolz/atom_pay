<!doctype html>
<html lang="en">
  <head>
    <title>Atom Paynetz</title>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  </head>
  <body>
   <?php 
    // echo "<pre>";print_r($_POST);die;
    $merchTxnId = uniqId();
    $amount = $_POST['amount'];
    // $amount = 100;
      
    require_once 'AtomAES.php';
     
    $atomenc = new AtomAES();
 
    $curl = curl_init();
      
    $jsondata = '{
	  "payInstrument": {
            "headDetails": {
              "version": "OTSv1.1",      
              "api": "AUTH",  
              "platform": "FLASH"	
            },
            "merchDetails": {
              "merchId": "435210",
              "userId": "435210",
              "password": "967f4144",
              "merchTxnId": "'. $merchTxnId .'",      
              "merchTxnDate": "'.date("Y-m-d H:i:s").'"
            },
            "payDetails": {
              "amount":  "'. $amount .'",
              "product": "SOCIETY",
              "custAccNo": "5149635991",
              "txnCurrency": "INR"
            },	
            "custDetails": {
              "custEmail": "'.$_POST['email'].'",
              "custMobile": "'.$_POST['mobile'].'"
            },
            "extras": {
              "udf1":"'.$_POST['month'].'",
              "udf2":"'.$_POST['course_year'].'_'.$_POST['installment_yr'].'",
              "udf3":"'.$_POST['branch_id'].'",
              "udf4":"'.$_POST['dept_id'].'",
              "udf5":"'.$_POST['instalment_fees_id'].'_'.$_POST['st_spc_id'].'_'.$_POST['pay_idss'].'_'.$_POST['st_idd'].'"
              
             
            }
	     }  
	   }';
    // echo "<pre>";print_r($jsondata);die;
   $encData = $atomenc->encrypt($jsondata,'874EDD6041E35C67D2D15E8E87C53B74', '874EDD6041E35C67D2D15E8E87C53B74');
        
    curl_setopt_array($curl, array(
      CURLOPT_URL => "https://payment1.atomtech.in/ots/aipay/auth?",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_SSL_VERIFYHOST => 2,
      CURLOPT_SSL_VERIFYPEER => 1,
      CURLOPT_CAINFO => getcwd() . '/cacert.pem',
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => "encData=".$encData."&merchId=435210",
      CURLOPT_HTTPHEADER => array(
        "Content-Type: application/x-www-form-urlencoded"
      ),
    ));
   
    $atomTokenId = null;
    $response = curl_exec($curl);   

    $getresp = explode("&", $response); 

    $encresp = substr($getresp[1], strpos($getresp[1], "=") + 1);       
    //echo $encresp;

    $decData = $atomenc->decrypt($encresp, 'E4916976CD38141B610CEA22110408D3', 'E4916976CD38141B610CEA22110408D3');

    //echo $decData;      

    if(curl_errno($curl)) {
        $error_msg = curl_error($curl);
        echo "error = ".$error_msg;
    }      

    if(isset($error_msg)) {
        // TODO - Handle cURL error accordingly
        echo "error = ".$error_msg;
    }   
      
    curl_close($curl);

    $res = json_decode($decData, true);   

    if($res){
      if($res['responseDetails']['txnStatusCode'] == 'OTS0000'){
        $atomTokenId = $res['atomTokenId'];
      }else{
        echo "Error getting data";
         $atomTokenId = null;
      }
    }
    ?>
    <div class="container token-money">

    <figure class="token-fig">
      <img src="https://genex.campusjadugar.com/studentAssetsNew/images/Logo.png" alt="">
    </figure>
      <h3 class="">Student Fees</h3>
      <!-- <p><span>Transaction Id:</span> <?= $merchTxnId ?></p> -->
      <!-- <p>Atom Token Id: <?= $atomTokenId ? $atomTokenId : "No Token" ?></p> -->
      <p><span>Pay Rs.</span> <?= $amount ?></p>
      <a name="" id="" class="btn btn-primary token-btn" href="javascript:openPay()" role="button">Pay Now</a>
    </div>
     <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
     <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
     <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

   <!-- <script src="https://pgtest.atomtech.in/staticdata/ots/js/atomcheckout.js"></script>  -->
   <script src="https://psa.atomtech.in/staticdata/ots/js/atomcheckout.js"></script>  
   <!-- for production -->
    <script>
    function openPay(){
        console.log('openPay called');
        const options = {
          "atomTokenId": "<?= $atomTokenId ?>",
          "merchId": "435210",
          "custEmail": "<?= $_POST['email']?>",
          "custMobile": "<?= $_POST['mobile']?>",
          "returnUrl":"https://genex.campusjadugar.com/AIPAY-CORE_PHP/response.php"
        }
        let atom = new AtomPaynetz(options,'uat');
    }

    </script>
   <!-- END -->
  </body>
</html>

<style>

.token-money{
  max-width: 500px;
  margin: 100px auto 0;
  background: linear-gradient(88.53deg, #2E3190 7.7%, #FF000F 129.21%);
  box-shadow: 0px 7px 47px #e1e7ff;
  border-radius: 12px;
  padding: 18px 60px 50px;
}

.token-money h3{
  font-size: 35px;
  font-weight: 700;
  color: white;
}

.token-money p{
  font-size: 17px;
  color: white;
}

.token-money p span{
  font-weight: 600;
}

.token-btn{
  border: 1px solid #e91b27;
  border-radius: 100px;
  font-size: 15px;
  font-weight: 600;
  display: inline-block;
  text-align: center;
  color: #2A2345;
  background: white;
  padding: 5px 25px;
  transition: 0.4s all
}

.token-btn:hover{
  border: 1px solid white;
  background: linear-gradient(88.53deg, #2E3190 7.7%, #FF000F 129.21%);
  color: white;
}

.token-fig{
  width: 160px;
  margin: 10px auto 30px;
}

.token-fig img{
  width: 100%;
}

</style>