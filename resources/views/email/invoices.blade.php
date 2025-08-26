@slot('header')
@endslot
@php
// Today
$today = new DateTime("today 08:00:00");
$todaydate = $today->format('d M, Y');

// Yesterday
$yesterday = new DateTime("yesterday 08:00:00");
$yesterdaydate = $yesterday->format('d M, Y');
@endphp
<tr>
	<td>
		<table width="90%" style="background: #fff; padding: 20px; margin-bottom:10px;" align="center">
			<tr>
				<p style="padding:0;maring:0;">
			        Dear Team, <br>
                    <br>
                    Good Morning!<br>
                    <br>
                    I’m Tamkeen Stores auto invoice email sender.<br>
                    I would like to share the invoices between {{ $yesterdaydate }} to {{ $todaydate }} - 8 AM to 8 AM<br>
                    
                    I would like to request you to cross check for a safe side.<br>
                    <br>
                    Thanks<br>
                    Tamkeen Stores Online<br>
                    <small>This email is system generated.</small>
			    </p>
			</tr>
		</table>
	</td>
</tr>
@slot('footer')
@endslot