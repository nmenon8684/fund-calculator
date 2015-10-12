<?php 
	require __DIR__ . '/vendor/autoload.php';

$independence_day = date('Y-m-d', strtotime("july 4 2015"));

echo $independence_day->modify('last friday');

//exit;

	function is_weekend($date)
    {
    	if((date('N', strtotime($date->format('Y-m-d'))) >= 6))
		{
			$date->modify('next monday');
		}

		return $date;
    }


	function if_market_closed($date)
	{
		$year = $date->format('Y');

		// new years day
		if($date->format('m-d') == '01-01')
		{
			$date->modify('next day');
		}

		// martin luther kings day
		if($date->format('Y-m-d') == date('Y-m-d', strtotime("january $year third monday")))
		{
			$date->modify('next day');
		}

		// presidents day
		if($date->format('Y-m-d') == date('Y-m-d', strtotime("february $year third monday")))
		{
			$date->modify('next day');
		}

		// good friday
		if($date->format('Y-m-d') == date("Y-m-d", strtotime( "+".(easter_days($year) - 2)." days", strtotime("$year-03-21 12:00:00"))))
		{
			$date->modify('next day');
		}

		// memorial day
		if($date->format('Y-m-d') == date('Y-m-d', strtotime("last Monday of May $year")))
		{
			$date->modify('next day');
		}

		// independence day
		$independence_day = date('Y-m-d', strtotime("july 4 $year"));

		//if($date->format('Y-m-d') == ( is_weekend($independence_day) ? $independence_day) )
		//{
			//print 'sup';
			//exit;
			//$date->modify('next day');
		//}

		// labor day

		// thanksgiving

		// christmas

		// saturday or sunday
		if(is_weekend($date))
		{
			$date->modify('next monday');
		}

		return $date->format('Y-m-d');
	}

	$client = new \Scheb\YahooFinanceApi\ApiClient();

	$symbol = 'FLGEX';
	$start_date = new DateTime('2015-07-03');
	$end_date = new DateTime('NOW');

	$data = $client->getHistoricalData($symbol, $start_date, $end_date);

	print '<pre>';
	//print_r($data);
	print '</pre>';

	$interval = DateInterval::createFromDateString('first day of next month');
	$period = new DatePeriod($start_date, $interval, $end_date, DatePeriod::EXCLUDE_START_DATE);

	$trans_date[] = if_market_closed($start_date);

	$start_date_day = $start_date->format('d');	

	if($start_date_day < 15)
	{
		$days_till_fifteenth =  15 - $start_date_day;

		$fifteenth_day = $start_date->add(new DateInterval('P' . $days_till_fifteenth . 'D'));

		$trans_date[] = if_market_closed($fifteenth_day);
	}

	foreach($period as $date)
	{
		$trans_date[] = if_market_closed($date);

		$date->add(new DateInterval('P14D'));

		$trans_date[] = if_market_closed($date);
	}

	print '<pre>';
	print_r($trans_date);
	print '</pre>';


	$funds = [];

	if(!empty($data['query']['results']['quote']))
	{
		foreach($data['query']['results']['quote'] as $quote)
		{
			//print '<pre>';
			//print_r($quote);
			//print '</pre>';
			//exit;
			if(in_array($quote['Date'], $trans_date))
			{
				$funds[$symbol][] = [
					'date' => $quote['Date'],
					'price' => $quote['Close'], 
				];
			}
		}
	}


	print '<pre>';
	print_r($funds);
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
