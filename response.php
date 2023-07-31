<?php
  include("db_con.php");
  require_once 'AtomAES.php'; 
  $atomenc = new AtomAES();
?>
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
      
              $data = $_POST['encData'];
          
              $decrypted = $atomenc->decrypt($data, 'E4916976CD38141B610CEA22110408D3', 'E4916976CD38141B610CEA22110408D3');
            //   echo "<pre>".$decrypted;

              $jsonData = json_decode($decrypted, true);

            //   echo "<pre>";print_r($jsonData);

              // echo "<pre>";print_r($jsonData['payInstrument']['extras']);

              $explodeData = explode("_",$jsonData['payInstrument']['extras']['udf5']);

              $instal_month = $jsonData['payInstrument']['extras']['udf1'];

              $course_and_install_yr = explode("_",$jsonData['payInstrument']['extras']['udf2']);

              $course_wise_install_yr = $course_and_install_yr[0];

              $year_wise_install_yr =  $course_and_install_yr[1];

              $branch_id = $jsonData['payInstrument']['extras']['udf3'];

              $dept_id = $jsonData['payInstrument']['extras']['udf4'];

              $instalment_fees_id = $explodeData[0];

              $student_spc_fees_id = $explodeData[1];

              $fees_title_id = $explodeData[2];

              $student_id = $explodeData[3];

              $transaction_id = $jsonData["payInstrument"]["payDetails"]["atomTxnId"];

              $transaction_amount = $jsonData['payInstrument']['payDetails']['amount'];

              $cust_acc = $jsonData['payInstrument']['payDetails']['custAccNo'];

              $txn_date = $jsonData['payInstrument']['payDetails']['txnCompleteDate'];

              $bank_txn_id = $jsonData['payInstrument']['payModeSpecificData']['bankDetails']['bankTxnId'];

              $bank_name = $jsonData['payInstrument']['payModeSpecificData']['bankDetails']['otsBankName'];

              $card_type = $jsonData['payInstrument']['payModeSpecificData']['bankDetails']['cardType'];

              $card_no = $jsonData['payInstrument']['payModeSpecificData']['bankDetails']['cardMaskNumber'];

              $scheme = $jsonData['payInstrument']['payModeSpecificData']['bankDetails']['scheme'];

              $cust_mail  = $jsonData['payInstrument']['custDetails']['custEmail'];

              $cust_mobile = $jsonData['payInstrument']['custDetails']['custMobile'];

              $status_code = $jsonData['payInstrument']['responseDetails']['statusCode'];

              $message = $jsonData['payInstrument']['responseDetails']['message'];

            //   $s = $txn_date[0];

            //   $dt = new DateTime($s);

              $date = date("Y-m-d",strtotime($txn_date));

            //   // echo $date;die;
            //   $time = $dt->format('H:i:s');


              // echo $transaction_id." ".$transaction_amount." ".$cust_acc." ".$txn_date." ".$bank_txn_id." ".$bank_name." ".$card_type." ".$card_no." ".$scheme." ".$cust_mail." ".$cust_mobile." ".$status_code." ".$message;

              $ladger_id = "SELECT b.tally_lager_id FROM tbl_student_info a JOIN tbl_tally_info b ON a.college_enrollment_no = b.college_enrollment_no WHERE a.id = $student_id";

			  $ladger_query = mysqli_query($conn,$ladger_id);

			  $tally_ladger_data = mysqli_fetch_row($ladger_query);

			  $st_tally_ladger_id = $tally_ladger_data[0];



              //---insert into tbl_atom_txn_details---//
              $insert_tbl_atom_txn_details = "INSERT INTO `tbl_atom_txn_details`(`atomTxnId`, `amount`, `custAccNo`, `txnCompleteDate`, `bankTxnId`, `otsBankName`, `cardType`, `cardMaskNumber`, `scheme`, `custEmail`, `custMobile`, `statusCode`, `message`, `student_id`,`tally_lager_id`) VALUES ('$transaction_id','$transaction_amount','$cust_acc','$txn_date','$bank_txn_id','$bank_name','$card_type','$card_no','$scheme','$cust_mail','$cust_mobile','$status_code','$message','$student_id','$st_tally_ladger_id')";

			//   echo $insert_tbl_atom_txn_details;die;

              $query_tbl_atom_txn_details = mysqli_query($conn,$insert_tbl_atom_txn_details);
              ////----end insert-----///
              
             if($jsonData['payInstrument']['responseDetails']['statusCode'] == 'OTS0000'){
              $status = 1;
             }else{
              $status = 0;
             }



        /*--------------insert records in fees tables----------------------------*/   
        
        $student_reg_id = "SELECT college_enrollment_no FROM `tbl_student_info` WHERE id=".$student_id."";
   
	$result = mysqli_query($conn,$student_reg_id);

	$student_clg_id = mysqli_fetch_row($result);

	// echo "<pre>";print_r($student_clg_id);

	////////////////////for exsist instalment fees////////////////////////////////

	$sql2 = "SELECT * FROM `tbl_fees_entry` WHERE branch_id = ".$branch_id." AND dept_id = ".$dept_id." AND fees_structure_id =".$fees_title_id." AND instalment_fees_id =".$instalment_fees_id." AND student_reg_id LIKE '".$student_clg_id[0]."' ";

	// echo $sql2;

	$result2 = mysqli_query($conn,$sql2);

	$exsist_instalment = mysqli_fetch_assoc($result2);

	// echo "<pre>";print_r($exsist_instalment);


	////////////////////for exsist student wise fees////////////////////////

	$sql3 = "SELECT * FROM `tbl_fees_entry` WHERE branch_id = ".$branch_id." AND dept_id = ".$dept_id." AND fees_structure_id =".$fees_title_id." AND st_spc_id =".$student_spc_fees_id." AND student_reg_id LIKE '".$student_clg_id[0]."' ";

	// echo $sql2;

	$result3 = mysqli_query($conn,$sql3);

	$exsist_student_wise_fees = mysqli_fetch_assoc($result3);

if($status == 1){
	// echo "ok";die;
  ///////////tbl_installment_payment data//////////
	$instalment_id_wise_data = "SELECT * FROM `tbl_installment_payment` WHERE id = $instalment_fees_id";

	$instalment_result = mysqli_query($conn,$instalment_id_wise_data);
	$instalment_data = mysqli_fetch_assoc($instalment_result);

	if(!empty($instalment_data['installment_yr']) || $instalment_data['installment_yr']!=0){
		$instalmentyear = $instalment_data['installment_yr'];
		$course_year = 0;
	}
	
	else if(!empty($instalment_data['course_year'])|| $instalment_data['course_year']!=0){
		$instalmentyear = 0;
		$course_year = $instalment_data['course_year'];
	}
	else {
		$instalmentyear = 0;
		$course_year = 0;

	}

	if(!empty($instalment_data['installment_sem']) || $instalment_data['installment_sem']!=0){
		$semester = $instalment_data['installment_sem'];
	}else{
		$semester = 0;
	}

	if(!empty($instalment_data['course_year'])|| $instalment_data['course_year']!=0){
		$course_year = $instalment_data['course_year'];
	}
	else{
		$course_year = 0;
	}

	if(!empty($instalment_data['month'])|| $instalment_data['month']!=0){
		$month = $instalment_data['month'];
	}
	else{
		$month = 0;
	}





///////////////////tbl_student_wise_fees data////////////////
	$st_spc_fees_id_wise_data = "SELECT * FROM `tbl_student_wise_fees` WHERE id = $student_spc_fees_id";

	$st_spc_result = mysqli_query($conn,$st_spc_fees_id_wise_data);

	$st_spc_data =mysqli_fetch_assoc($st_spc_result);

	// echo "<pre>";print_r($instalment_data);

	if(!empty($exsist_instalment)){

		$pay_amount = $exsist_instalment['pay_amount'] + $transaction_amount;

		if(!empty($instalmentyear)|| $instalmentyear != 0){
			$year = $instalmentyear;
		}
		else if(!empty($course_year)||$course_year != 0){
			$year = $course_year;
		}
		else{
			$year = 0;
		}

		$update_fees_entry_for_instalment = "UPDATE `tbl_fees_entry` SET branch_id=$branch_id, dept_id = $dept_id, year =$year, semester = ".$instalment_data['installment_sem'].", month = ".$exsist_instalment['month'].", instalment_fees_id = $instalment_fees_id,fees_structure_id = $fees_title_id, student_reg_id = '$student_clg_id[0]',session='".$instalment_data['session']."', payment_mode = 3, fees_amount = ".$instalment_data['amount'].", due_amount = 0, pay_amount = $pay_amount, fine_amount= 0,scholarship_amount = 0 ,is_counter=1, transaction_id = $transaction_id, payment_date = '$date', status = '$status',updated_at = '$txn_date' WHERE instalment_fees_id = $instalment_fees_id AND student_reg_id = '$student_clg_id[0]'";

		// echo

		mysqli_query($conn,$update_fees_entry_for_instalment);

		// echo $update_fees_entry_for_instalment."<br>";

		$insert_fees_entry_structure = "INSERT INTO tbl_fees_entry_structure(`branch_id`,`dept_id`,`year`,`course_year`,`semester`,`month`,`student_id`,`fees_entry_id`,`fees_structure_id`,`amount`,`total`,`session`,`pay_mode`,`payment_date`,`status`) VALUES ('$branch_id','$dept_id','$instalmentyear','$course_year','".$instalment_data['installment_sem']."','$month','$student_clg_id[0]','".$exsist_instalment['id']."','$fees_title_id','$transaction_amount','$transaction_amount','".$instalment_data['session']."','5','$date','$status')";

		mysqli_query($conn,$insert_fees_entry_structure);

		// echo $insert_fees_entry_structure."<br>";
       


		$insert_payment_mode_history = "INSERT INTO tbl_payment_mode_history (`fees_entry_id`,`branch_id`,`dept_id`,`year`,`semester`,`month`,`student_reg_id`,`session`,`bank_name`,`transaction_id`,`payment_date`,`type`) VALUES ('".$exsist_instalment['id']."','$branch_id','$dept_id','$year','".$instalment_data['installment_sem']."','$month','$student_clg_id[0]','".$instalment_data['session']."','$bank_name','$transaction_id','$date','1')";

		// echo $insert_payment_mode_history."<br>";

		// mysqli_query($conn,$insert_payment_mode_history);





	}
	else if(!empty($exsist_student_wise_fees)){


		$pay_amount = $exsist_student_wise_fees['pay_amount']+$transaction_amount;
		

		$update_fees_entry_for_student_wise = "UPDATE `tbl_fees_entry` SET branch_id=$branch_id, dept_id = $dept_id, st_spc_id = $student_spc_fees_id,fees_structure_id = $fees_title_id, student_reg_id = '$student_clg_id[0]',session=".$st_spc_data['session'].", payment_mode = 3, fees_amount = ".$st_spc_data['amount'].", due_amount=0,pay_amount= $pay_amount, fine_amount=0,scholarship_amount = 0 ,is_counter=1, transaction_id = $transaction_id, payment_date = '$date',status = $status,updated_at = '$txn_date'  WHERE st_spc_id = $student_spc_fees_id AND student_reg_id = '$student_clg_id[0]'";

		mysqli_query($conn,$update_fees_entry_for_student_wise);

		// echo $update_fees_entry_for_student_wise."<br>";

		$insert_fees_entry_structure = "INSERT INTO tbl_fees_entry_structure(`branch_id`,`dept_id`,`student_id`,`fees_entry_id`,`fees_structure_id`,`amount`,`total`,`session`,`pay_mode`,`payment_date`,`status`) VALUES ('$branch_id','$dept_id','$student_clg_id[0]','".$st_spc_data['id']."','".$exsist_student_wise_fees['id']."','$transaction_amount','$transaction_amount','".$st_spc_data['session']."','5','$date','$status')";

		mysqli_query($conn,$insert_fees_entry_structure);

		// echo $insert_fees_entry_structure;


		$insert_payment_mode_history = "INSERT INTO tbl_payment_mode_history(`fees_entry_id`,`branch_id`,`dept_id`,`student_reg_id`,`session`,`bank_name`,`transaction_id`,`payment_date`,`type`) VALUES ('".$st_spc_data['id']."','$branch_id','$dept_id','$student_clg_id[0]','".$st_spc_data['session']."','$bank_name','$transaction_id','$date','1')";

		mysqli_query($conn,$insert_payment_mode_history);

		// echo $insert_payment_mode_history;


	}
	else {
		
		// echo "ok1";die;

		$installment_month = $instal_month;
		// $course_yr = $course_wise_install_yr;
		// $insallment_yr = $year_wise_install_yr;

		
		if($year_wise_install_yr==''){
			$year = 0;
			$insallment_yr = 0;
		}else{
			$year = $year_wise_install_yr;
			$insallment_yr = $year_wise_install_yr;
		}

		if($course_wise_install_yr==''){
			$year = 0;
			$course_yr = 0;
		}
		else{
			$year = $course_wise_install_yr;
			$course_yr =$course_wise_install_yr;
		}


		if(empty($explodeData[0])|| $explodeData[0]==''){
			$instalment_fees_id =0;
		}
		else{
			$instalment_fees_id = $explodeData[0];
		}

		if(empty($explodeData[1])|| $explodeData[1]==''){
			$student_spc_fees_id = 0;
		}
		else{
			$student_spc_fees_id = $explodeData[1];
		}

		/////get admission details////

		$admission = "SELECT * FROM tbl_branch_dept_fees_structure WHERE fees_type = 10 AND WHERE pay_account_id = $fees_title_id";

		$admission_result = mysqli_query($conn,$admission);
		$admission_data = mysqli_fetch_assoc($admission_result);


	   /////////get instalment fees details///////////

	   $instalment = "SELECT * FROM tbl_installment_payment WHERE id = $instalment_fees_id AND s_id = $student_id AND  installment_yr = $insallment_yr AND course_year = $course_year ";

	//    echo $instalment;

	   $instalment_query = mysqli_query($conn,$instalment);

	   $instalment_student = mysqli_fetch_assoc($instalment_query);

	//    echo "<pre>";print_r($instalment_student);die;

	  

	  
	   //////////get student specific fees///////////

	   $student_specific = "SELECT * FROM tbl_student_wise_fees WHERE id = $student_spc_fees_id AND branch_id = $branch_id AND dept_id = $dept_id AND st_id = $student_id ";

	   $student_specific_query = mysqli_query($conn,$student_specific);
	   $student_specific_data = mysqli_fetch_assoc($student_specific_query);



	   if(!empty($admission_data)){

		// echo "<pre>";print_r($admission_data);

		$insert_fees_entry = "INSERT INTO `tbl_fees_entry`(`branch_id`, `dept_id`, `year`, `semester`, `month`, `instalment_fees_id`, `st_spc_id`, `fees_structure_id`, `student_reg_id`, `session`, `payment_mode`, `fees_type`, `fees_amount`, `due_amount`, `pay_amount`, `fine_amount`, `discounted_fine_amount`, `scholarship_amount`, `is_counter`, `transaction_id`, `payment_date`, `status`, `created_at`,`updated_at`,`uploaded_via`, `check_bounce_charge`) VALUES ($branch_id,$dept_id,0,0,0,0,0,$fees_title_id,'$student_clg_id[0]','".$admission_data['session']."',3,10,".$admission_data['amount'].",00,$transaction_amount,00,00,00,0,$transaction_id,'$date',$status,'$txn_date','$txn_date',0,00)";



		mysqli_query($conn,$insert_fees_entry);


		$last_fees_entry_id = $conn->insert_id;

		$insert_fees_entry_structure = "INSERT INTO `tbl_fees_entry_structure`(`branch_id`, `dept_id`, `year`, `course_year`, `semester`, `month`, `student_id`, `fees_entry_id`, `fees_structure_id`, `amount`, `fine_amount`, `scholarship_amount`, `total`, `pay_term`, `session`, `pay_mode`, `payment_date`,`status`, `is_multi`, `uploaded_via`, `check_bounce_charge`) VALUES ($branch_id,$dept_id,0,0,0,0,'$student_clg_id[0]',$last_fees_entry_id,$fees_title_id,$transaction_amount,0,0,$transaction_amount,0,'".$admission_data['session']."',5,'$date',$status,0,0,0)";

		mysqli_query($conn,$insert_fees_entry_structure);


		$insert_payment_mode_history = "INSERT INTO `tbl_payment_mode_history`(`fees_entry_id`, `branch_id`, `dept_id`, `year`, `semester`, `month`, `student_reg_id`, `session`, `bank_name`,`transaction_id`, `payment_date`,`added_by`, `add_on`, `type`) VALUES ($last_fees_entry_id,$branch_id,$dept_id,0,0,0,'$student_clg_id[0]','".$admission_data['session']."','$bank_name','$transaction_id','$date',1,'$txn_date',1,)";

		mysqli_query($conn,$insert_payment_mode_history);

	   }

	   else if(!empty($instalment_student)){




		$insert_fees_entry = "INSERT INTO `tbl_fees_entry`(`branch_id`, `dept_id`, `year`, `semester`, `month`, `instalment_fees_id`, `st_spc_id`, `fees_structure_id`, `student_reg_id`, `session`, `payment_mode`, `fees_type`, `fees_amount`, `due_amount`, `pay_amount`, `fine_amount`, `discounted_fine_amount`, `scholarship_amount`, `is_counter`, `transaction_id`, `payment_date`, `status`, `created_at`,`updated_at`,`added_by`, `uploaded_via`, `check_bounce_charge`) VALUES ($branch_id,$dept_id,$year,".$instalment_student['installment_sem'].",".$instalment_student['month'].",$instalment_fees_id,$student_spc_fees_id,$fees_title_id,'$student_clg_id[0]','".$instalment_student['session']."',3,".$instalment_student['installment_type'].",".$instalment_student['amount'].",0,$transaction_amount,0,0,0,0,$transaction_id,'$date',$status,'$txn_date','$txn_date',1,0,00)";

		// echo $txn_date[0]."<br>";
		// echo $insert_fees_entry;die;

		mysqli_query($conn,$insert_fees_entry);

		$last_fees_entry_id = $conn->insert_id;

		$insert_fees_entry_structure = "INSERT INTO `tbl_fees_entry_structure`(`branch_id`, `dept_id`, `year`, `course_year`, `semester`, `month`, `student_id`, `fees_entry_id`, `fees_structure_id`,`amount`, `fine_amount`, `scholarship_amount`, `total`, `pay_term`, `session`, `pay_mode`, `payment_date`,`status`, `is_multi`, `uploaded_via`, `check_bounce_charge`) VALUES ($branch_id,$dept_id,$year_wise_install_yr,$course_wise_install_yr,".$instalment_student['installment_sem'].",".$instalment_student['month'].",'$student_clg_id[0]',$last_fees_entry_id,$fees_title_id,$transaction_amount,0,0,$transaction_amount,0,'".$instalment_student['session']."',5,'$date',$status,0,0,0)";

		// echo $insert_fees_entry_structure;


		mysqli_query($conn,$insert_fees_entry_structure);

		$insert_payment_mode_history = "INSERT INTO `tbl_payment_mode_history`(`fees_entry_id`, `branch_id`, `dept_id`, `year`, `semester`, `month`, `student_reg_id`, `session`, `bank_name`,`transaction_id`, `payment_date`,`added_by`, `add_on`, `type`) VALUES ($last_fees_entry_id,$branch_id,$dept_id,$year,".$instalment_student['installment_sem'].",".$instalment_student['month'].",'$student_clg_id[0]','".$instalment_student['session']."','$bank_name','$transaction_id','$date',1,'$txn_date',1)";

		// echo $insert_payment_mode_history;die;

		mysqli_query($conn,$insert_payment_mode_history);

	   }

	   else if(!empty($student_specific_data)){

		$insert_fees_entry = "INSERT INTO `tbl_fees_entry`(`branch_id`, `dept_id`, `year`, `semester`, `month`, `instalment_fees_id`, `st_spc_id`, `fees_structure_id`, `student_reg_id`, `session`, `payment_mode`, `fees_type`, `fees_amount`, `due_amount`, `pay_amount`, `fine_amount`, `discounted_fine_amount`, `scholarship_amount`, `is_counter`, `transaction_id`, `payment_date`, `status`, `created_at`,`updated_at`,`added_by`, `uploaded_via`, `check_bounce_charge`) VALUES ($branch_id,$dept_id,0,0,0,$instalment_fees_id,$student_spc_fees_id,$fees_title_id,'$student_clg_id[0]','".$student_specific_data['session']."',3,0,".$student_specific_data['amount'].",0,$transaction_amount,0,0,0,0,$transaction_id,'$date',$status,'$txn_date','$txn_date',1,0,00)";

		mysqli_query($conn,$insert_fees_entry);

		$last_fees_entry_id = $conn->insert_id;

		$insert_fees_entry_structure = "INSERT INTO `tbl_fees_entry_structure`(`branch_id`, `dept_id`, `year`, `course_year`, `semester`, `month`, `student_id`, `fees_entry_id`, `fees_structure_id`,`amount`, `fine_amount`, `scholarship_amount`, `total`, `pay_term`, `session`, `pay_mode`, `payment_date`,`status`, `is_multi`, `uploaded_via`, `check_bounce_charge`) VALUES ($branch_id,$dept_id,0,0,0,0,'$student_clg_id[0]',$last_fees_entry_id,$fees_title_id,$transaction_amount,0,0,$transaction_amount,0,'".$student_specific_data['session']."',5,'$date',$status,0,0,0)";


		mysqli_query($conn,$insert_fees_entry_structure);

		$insert_payment_mode_history = "INSERT INTO `tbl_payment_mode_history`(`fees_entry_id`, `branch_id`, `dept_id`, `year`, `semester`, `month`, `student_reg_id`, `session`, `bank_name`,`transaction_id`, `payment_date`,`added_by`, `add_on`, `type`) VALUES ($last_fees_entry_id,$branch_id,$dept_id,0,0,0,'$student_clg_id[0]','".$student_specific_data['session']."','$bank_name','$transaction_id','$date',1,'$txn_date',1,)";

		mysqli_query($conn,$insert_payment_mode_history);

	   }



		



		

	}
}
	

      
      ?>
      
      <div class="new-table">
	<table class="stu-table pro-table">
		<thead>
			<tr>
				<th><span style="display: block; width: 250px;">Description</span></th>
				<th>Details</th>
			</tr>
		</thead>
		<tbody>
			<?php 
				if (isset($_POST) && count($_POST)>0 )
					{ ?>
					
					<tr>
						<td>Transaction result :</td>
						<td><?php echo $jsonData['payInstrument']['responseDetails']['message'];?></td>
					</tr>

					<!-- <tr>
						<td>Merchant transaction ID :</td>
						<td><?php echo $jsonData['payInstrument']['merchDetails']['merchTxnId'];?></td>
					</tr> -->

					<tr>
						<td>Transaction date :</td>
						<td><?php echo $jsonData['payInstrument']['merchDetails']['merchTxnDate'];?></td>
					</tr>

					<tr>
						<td>Bank transaction ID :</td>
						<td><?php echo $jsonData['payInstrument']['payModeSpecificData']['bankDetails']['bankTxnId'];?></td>
					</tr>

          <tr>
						<!-- <td>Bank Name</td>
						<td><?php echo $bank_name;?></td> -->
						<td>Transaction Amount :</td>
						<td><?php echo $transaction_amount;?></td>
					</tr>

					<tr>
						<td>Currency</td>
						<td><?php echo $jsonData['payInstrument']['payDetails']['txnCurrency'];?></td>
					</tr>					

					<tr>
						<td>Status</td>
						<td><?php echo $message;?></td>
					</tr>						

				<?php	}
				else {?>
				<tr>
					<td><?php echo "<b>Checksum mismatched.</b>";
					//Process transaction as suspicious.?></td>
				</tr>
					
			<?php	}
			
			?>		

		</tbody>
	</table>

	<a href="https://genex.campusjadugar.com/student-fees" class="back-btn"> Back</a>
</div>


<style>

	.new-table{
		padding: 60px 0;
	}

	.stu-table thead tr th{
		padding: 0 15px;
    	font-size: 15px;
		background: #383B83;
    	color: #fff;
		text-align: left;
	}

	.stu-table {
		width: 50%;
		margin: 0 auto;
		box-shadow: 0px 7px 47px #e1e7ff;
		padding: 0;
		border-spacing: 0;
	}

	.stu-table tr{
		height: 40px;
	}

	.stu-table tr td{
		padding: 0 15px;
    	font-size: 14px;
		word-break: break-all;
	}

	.pro-table tr:nth-child(even) {
    	background: #fff !important;
	}

	.pro-table tr:nth-child(odd) {
    	background: #F9FAFE;
	}

	.back-btn{
		height: 42px;
		line-height: 42px;
		font-size: 14px;
		width: 210px;
		background: linear-gradient(84.52deg, #131ABC -16.62%, #181B66 111.91%);
		color: #FFFFFF;
		letter-spacing: 0.1em;
		font-weight: bold;
		border: 1px solid #b6113c;
		text-transform: uppercase;
		display: block;
		text-align: center;
		text-decoration: none;
		border-radius: 100px;
		margin: 25px auto 0;
		transition: 0.3s all
	}

	.back-btn:hover{
		opacity: 0.8;
	}

</style>
  
    </body>
</html>