<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Attestation de Formation - {{ $enrollment->client->name }}</title>
    <style>
        body { font-family: 'Georgia', serif; color: #2d3748; padding: 40px; border: 10px double #1a365d; margin: 10px; height: 90%; }
        .container { text-align: center; }
        .logo-space { font-family: 'Helvetica Neue', Arial, sans-serif; font-size: 28px; font-weight: bold; color: #1a365d; letter-spacing: 4px; margin-bottom: 20px; }
        .title { font-size: 36px; font-weight: bold; color: #2b6cb0; margin-bottom: 30px; text-transform: uppercase; font-style: italic; }
        .certify-text { font-size: 18px; line-height: 1.8; margin-bottom: 40px; text-align: justify; padding: 0 40px; }
        .highlight { font-weight: bold; color: #1a365d; font-size: 20px; font-family: 'Helvetica Neue', Arial, sans-serif; }
        .meta-info { margin-top: 50px; width: 100%; font-family: 'Helvetica Neue', Arial, sans-serif; }
        .meta-info td { width: 50%; font-size: 14px; }
        .signature { font-style: italic; font-weight: bold; margin-top: 15px; color: #4a5568; }
        .rate-badge { display: inline-block; background-color: #ebf8ff; color: #2b6cb0; padding: 5px 15px; border-radius: 20px; font-weight: bold; font-family: Arial, sans-serif; font-size: 14px; margin-top: 10px; border: 1px solid #bee3f8; }
    </style>
</head>
<body>

    <div class="container">
        <div class="logo-space">HORIZON TRAINING CENTER</div>
        
        <div class="title">Attestation de Formation</div>

        <div class="certify-text">
            La direction du centre de formation professionnelle <strong>HORIZON</strong> certifie par la présente que 
            M/Mme/Mlle <span class="highlight">{{ $enrollment->client->name }}</span> a suivi avec assiduité et 
            complété avec succès la formation intitulée :
            <br><br>
            <center><span class="highlight" style="font-size: 22px; color: #2b6cb0;">« {{ $enrollment->course->title }} »</span></center>
            <br>
            Cette formation, d'une durée totale de <strong>{{ $enrollment->course->duration_hours }} heures</strong>, 
            a été dispensée par l'encadreur <strong>{{ $enrollment->course->trainer_name }}</strong> pour le niveau 
            <strong>{{ $enrollment->course->level }}</strong>.
        </div>

        <div class="rate-badge">
            Taux d'assiduité validé : {{ $attendance_rate }}% (Seuil requis $\ge$ 70%)
        </div>

        <table class="meta-info">
            <tr>
                <td style="text-align: left; padding-left: 40px;">
                    <strong>Identifiant apprenant :</strong> HZN-{{ str_pad($enrollment->client->id, 4, '0', STR_PAD_LEFT) }}<br>
                    <strong>Période :</strong> Du {{ date('d/m/Y', strtotime($enrollment->course->start_date)) }} au {{ date('d/m/Y', strtotime($enrollment->course->end_date)) }}
                </td>
                <td style="text-align: right; padding-right: 40px; vertical-align: top;">
                    Fait à Yaoundé, le {{ date('d/m/Y') }}<br>
                    <div class="signature">Le Directeur du Centre</div>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>