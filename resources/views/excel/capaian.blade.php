
<table border="1">
    <thead>
        <tr bgcolor="green">
            <td>No</td>
            @foreach(range(1, $capaian['soal']) as $index)
            <td align="center">{{ $index }}</td>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($capaian['pesertas'] as $data)
        <tr>
            <td vertical-align="middle">
                {{ $data['peserta']['nama'] }} <br>
                {{ $data['peserta']['no_ujian'] }}
            </td>
            @foreach($data['data'] as $nilai)
                <td align="center">{{ $nilai['iscorrect'] }}</td>
            @endforeach
        </tr>
        @endforeach
    </tbody>
</table>