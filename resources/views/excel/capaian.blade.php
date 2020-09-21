<table border="1" style="border: 1px solid #000000">
    <tr style="background-color: #08c45a; color: #FFFFFF">
        <td height="30"> No</td>
        @foreach(range(1, $soals->count()) as $index)
        <td align="center" height="30">{{ $index }}</td>
        @endforeach
        <td>Benar</td>
    </tr>
    @foreach($pesertas as $fil)
    <tr>
        <td valign="middle">
            {{ $fil['peserta']['nama'] }} <br>
            {{ $fil['peserta']['no_ujian'] }}
        </td>
        @php
            $benar = 0;
        @endphp
        @foreach($soals as $soal)
        <td valign="middle" align="center">
            @php
                $filtered = collect($fil['data'])->firstWhere('soal_id', $soal->id);
                if($filtered != '') {
                    $benar += $filtered->iscorrect;
                }
            @endphp
            {{ $filtered != '' ? $filtered->iscorrect : '-' }}
        </td>
        @endforeach
        <td valign="middle" align="center">
            {{ $benar }}
        </td>
    </tr>
    @endforeach
</table>