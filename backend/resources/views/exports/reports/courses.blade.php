<!DOCTYPE html>
<html>
<head>
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 8px; }
    </style>
</head>
<body>
    <h2>Synthèse des Formations</h2>
    <p>Revenus globaux : {{ number_format($total_revenues, 0, ',', ' ') }} FCFA</p>
    <table>
        <thead>
            <tr>
                <th>Formation</th>
                <th>Inscrits</th>
                <th>Assiduité Moyenne</th>
                <th>Revenus</th>
            </tr>
        </thead>
        <tbody>
            @foreach($courses_summary as $course)
            <tr>
                <td>{{ $course['title'] }}</td>
                <td>{{ $course['enrollments_count'] }}</td>
                <td>{{ $course['average_attendance_rate'] }}</td>
                <td>{{ number_format($course['revenues_generated'], 0, ',', ' ') }} FCFA</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>