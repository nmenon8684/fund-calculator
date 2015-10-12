<?php 
	require __DIR__ . '/vendor/autoload.php';

	$client = new \Scheb\YahooFinanceApi\ApiClient();

	/**
	 * Calculate the difference in months between two dates (v1 / 18.11.2013)
	 *
	 * @param \DateTime $date1
	 * @param \DateTime $date2
	 * @return int
	 */
	function diffInMonths(\DateTime $date1, \DateTime $date2)
	{
	    $diff =  $date1->diff($date2);

	    $months = $diff->y * 12 + $diff->m + $diff->d / 30;

	    return (int) round($months);
	}

	$symbol = 'FLGEX';
	$todays_date = new DateTime('NOW');
	$start_date_orig = new DateTime('2015-01-14');
	$start_date = $start_date_orig;
	$end_date = new DateTime('NOW');



/*print 'range: ' . $start_date->format('m-d-Y') . ' to ' . $end_date->format('m-d-Y');
print '<br>';

$number_of_months = diffInMonths($start_date, $end_date) + 1;

print 'months in range:';
print_r($number_of_months);
print '<br>';

$trans_date = [];

for ($x = 1; $x <= $number_of_months; $x++) 
{
    echo "The number is: $x <br>";
	$trans_date[] ;
} 

exit;
*/

	//$data = $client->getHistoricalData($symbol, $start_date, $end_date);

	//print '<pre>';
	//print_r($data);
	//print '</pre>';

	$interval = new DateInterval('P1M');

	if($start_date->format('d') < 15)
	{
	    $trans_date[] = $start_date->format('Y-m-d');

	    //$start_date = $start_date->modify('next month');
	    //$start_date = $start_date->setDate($start_date->format('Y'), $start_date->format('m'), 1);
	}

	$daterange = new DatePeriod($start_date, $interval ,$end_date);

	foreach($daterange as $indx => $date)
	{
		print '<pre>';
		print_r($start_date);
		print_r($date);
		print '</pre>';

		//if($date > $start_date_orig)
		//{
		 //   $trans_date[] = $date->setDate($date->format('Y'), $date->format('m'), 15)->format('Y-m-d');
		//}
		//else
		//{

			$date->setDate($date->format('Y'), $date->format('m'), 1);
		    
		    if($date > $start_date_orig)
		    {
			    $trans_date[] = $date->format('Y-m-d');
		    }

		    $fifteenth_of_month = $date->add(new DateInterval('P14D'));

		    if($fifteenth_of_month < $todays_date)
		    {
			    $trans_date[] = $fifteenth_of_month->format("Y-m-d");
		    }
		//}
	}

	print '<pre>';
	print_r($trans_date);
	print '</pre>';

?>



<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
<meta name="description" content="">
<meta name="author" content="">
<link rel="icon" href="../../favicon.ico">

<title>Grid Template for Bootstrap</title>

<!-- Bootstrap core CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">

<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
<!--[if lt IE 9]>
	<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
	<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->
</head>

<body>
	<div class="container">
		<table class="table table-bordered">
			<thead>
				<tr>
					<th>Symbol/Date</th>
					<th>Shares</th>
					<th>Current Value</th>
					<th>Total Gain($)</th>
					<th>Total Gain(%)</th>
					<th>Current Value</th>
				</tr>
			</thead>
			<tbody>
				<tr class="active">
					<td colspan="6">John</td>
				</tr>
				<tr>
					<td>01-01-2015</td>
					<td>100</td>
					<td>$1.00</td>
					<td>+$5.00</td>
					<td>+5%</td>
					<td>$105</td>
				</tr>
			</tbody>
		</table>
	</div>

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
</body>
</html>
