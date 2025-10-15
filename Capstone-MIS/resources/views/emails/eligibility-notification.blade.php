<p>Dear {{ $beneficiary->first_name }},</p>
<p>You are eligible to apply for the program <strong>{{ $aidProgram->aid_program_name }}</strong> scheduled from <strong>{{ $schedule->start_date }}</strong> to <strong>{{ $schedule->end_date }}</strong>.</p>
<p><strong>Requirements:</strong></p>
<ul>
    @foreach ($requirements as $req)
        <li>{{ $req }}</li>
    @endforeach
</ul>
<p>Please prepare the above documents and visit your barangay for more details.</p>
<p>Thank you!</p>
