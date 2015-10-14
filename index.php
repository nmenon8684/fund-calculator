<?php 
	require __DIR__ . '/vendor/autoload.php';

	function is_weekend($date)
    {
    	if((date('N', strtotime($date->format('Y-m-d'))) >= 6))
		{
			return true;
		}

		return false;
    }

    function adjust_fixed_holiday($date, $modify_date = 'next monday')
    {
    	$holiday = new DateTime($date);

		return (is_weekend($holiday) ? $holiday->modify($modify_date)->format('Y-m-d') : $holiday->format('Y-m-d'));
    }

    function gain_format($number, $type = 'dollar')
    {
    	// positive number
    	if($number >= 0)
    	{
    		$number = ($type == 'percent') ? '+' . number_format($number, 2) . '%' : '+$' . number_format($number, 2);
    	} 
    	//negative number
    	else
    	{
    		$number = ($type == 'percent') ? number_format($number, 2) . '%' : str_replace('-', '-$', number_format($number, 2));
    	}

    	return $number;
    }

	function is_market_closed($date)
	{
		$year = $date->format('Y');

		// federal holidays
		$federal_holidays = [
			adjust_fixed_holiday($year . '-01-01'), // new years day
			date('Y-m-d', strtotime("third Monday of January $year")), // martin luther kings day
			date('Y-m-d', strtotime("third Monday of February $year")), // presidents day
			date("Y-m-d", strtotime( "+".(easter_days($year) - 2)." days", strtotime("$year-03-21 12:00:00"))), // good friday
			date('Y-m-d', strtotime("last Monday of May $year")), // memorial day
			adjust_fixed_holiday($year . '-07-04', 'last friday'), // independence day
			date('Y-m-d', strtotime("first Monday of September $year")), // labor day
			date('Y-m-d', strtotime("last Thursday of November $year")), // thanksgiving
			adjust_fixed_holiday($year . '-12-25'), // christmas
		];


		if(in_array($date->format('Y-m-d'), $federal_holidays))
		{
			$date->modify('next day');
		}

		// saturday or sunday
		if(is_weekend($date))
		{
			$date->modify('next monday');
		}

		return $date->format('Y-m-d');
	}

	$client = new \Scheb\YahooFinanceApi\ApiClient();

	$symbol = 'FPHAX';
	$start_date = new DateTime('2015-09-01');
	$end_date = new DateTime('NOW');

	$history_nav = $client->getHistoricalData($symbol, $start_date, $end_date);

	$current_nav = $client->getQuotes($symbol);
	$current_nav = $current_nav['query']['results']['quote']['LastTradePriceOnly'];

	print '<pre>';
	print_r($history_nav);
	print '</pre>';
	//exit;

	$interval = DateInterval::createFromDateString('first day of next month');
	$period = new DatePeriod($start_date, $interval, $end_date, DatePeriod::EXCLUDE_START_DATE);

	$trans_date[] = is_market_closed($start_date);

	$start_date_day = $start_date->format('d');	

	if($start_date_day < 15)
	{
		$days_till_fifteenth =  15 - $start_date_day;

		$fifteenth_day = $start_date->add(new DateInterval('P' . $days_till_fifteenth . 'D'));

		$trans_date[] = is_market_closed($fifteenth_day);
	}

	foreach($period as $date)
	{
		$trans_date[] = is_market_closed($date);

		$date->add(new DateInterval('P14D'));
		//$date->setDate($date->format('Y'), $date->format('m'), 15);

		$trans_date[] = is_market_closed($date);
	}

	print '<pre>';
	//print_r($trans_date);
	print '</pre>';


	$funds = [];
	$funds_trans = [];

	if(!empty($history_nav['query']['results']['quote']))
	{
		$total_price = 0;
		$total_shares = 0;
		$total_current_value = 0;
		$total_investment_value = 0;
		$total_total_gain_dollar = 0;
		$total_total_gain_percent = 0;

		foreach($history_nav['query']['results']['quote'] as $quote)
		{
			//print '<pre>';
			//print_r($quote);
			//print '</pre>';
			//exit;
			if(in_array($quote['Date'], $trans_date))
			{
				$price_per_share = number_format($quote['Close'], 2);
				$shares = round( 100 / $price_per_share, 3);
				$current_value = number_format($shares * $current_nav, 2);
				$investment_value = round($shares * $quote['Close']);
				$total_gain_dollar = $current_value - $investment_value;
				$total_gain_percent = ($total_gain_dollar / $investment_value) * 100;

				$funds_trans[] =  [
					'date' => $quote['Date'],
					'price' => $price_per_share, 
					'shares' => $shares,
					'current_value' => $current_value,
					'investment_value' => $investment_value,
					'total_gain_dollar' => gain_format($total_gain_dollar),
					'total_gain_percent' => gain_format($total_gain_percent, 'percent'),
					'gain_loss_class' => ($total_gain_dollar >= 0) ? 'text-success' : 'text-danger', 
				];
				
				$total_price = ($total_price + $price_per_share) / count($funds_trans);
				$total_shares = $total_shares + $shares;
				$total_current_value = $total_current_value + $current_value;
				$total_investment_value = $total_investment_value + $investment_value;
				$total_total_gain_dollar = $total_total_gain_dollar + $total_gain_dollar;
				$total_total_gain_percent = ($total_total_gain_dollar / $total_investment_value) * 100;
			}
		}

		$funds[] = [
			'name' => $symbol,
			'price' => $total_price, 
			'shares' => $total_shares,
			'current_value' => $total_current_value,
			'investment_value' => $total_investment_value,
			'total_gain_dollar' => gain_format($total_total_gain_dollar),
			'total_gain_percent' => gain_format($total_total_gain_percent, 'percent'),
			'gain_loss_class' => ($total_total_gain_dollar >= 0) ? 'text-success' : 'text-danger', 
			'trans' => $funds_trans,
		];

	}


	print '<pre>';
	print_r($funds);
	print '</pre>';
exit;

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

<title>Fund Calculator</title>

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
					<th>Price Per Share</th>
					<th>Total Gain($)</th>
					<th>Total Gain(%)</th>
					<th>Current Value</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($funds as $fund_name => $fund_transactions): ?>
					<tr class="active">
						<td><?php echo $fund_name; ?></td>
						<td>XXX</td>
						<td>XXX</td>
						<td>XXX</td>
						<td>XXX</td>
						<td>XXX</td>
					</tr>
					<?php foreach($fund_transactions as $transaction): ?>
						<tr>
							<td><?php echo $transaction['date']; ?></td>
							<td><?php echo $transaction['shares']; ?></td>
							<td><?php echo $transaction['price']; ?></td>
							<td><span class="<?php echo $transaction['gain_loss_class']; ?>"><?php echo $transaction['total_gain_dollar']; ?></span></td>
							<td><span class="<?php echo $transaction['gain_loss_class']; ?>"><?php echo $transaction['total_gain_percent']; ?></span></td>
							<td><span class="<?php echo $transaction['gain_loss_class']; ?>">$<?php echo $transaction['current_value']; ?></span></td>
						</tr>
					<?php endforeach; ?>
				<?php endforeach; ?>		
				<tr class="success">
					<td colspan="3">TOTALS:</td>
					<td>XXX</td>
					<td>XXX</td>
					<td>XXX</td>
				</tr>		
			</tbody>
		</table>
	</div>

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
</body>
</html>
