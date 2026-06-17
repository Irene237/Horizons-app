<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Reçu d'inscription - {{ $enrollment->receipt_number }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #333; margin: 30px; line-height: 1.5; }
        .header { text-align: center; border-bottom: 2px solid #3b82f6; padding-bottom: 20px; margin-bottom: 30px; }
        .app-title { font-size: 24px; font-weight: bold; color: #3b82f6; text-transform: uppercase; }
        .doc-title { font-size: 18px; margin-top: 5px; color: #555; }
        .details-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .details-table td { padding: 10px; border: 1px solid #e5e7eb; }
        .details-table td.label { font-weight: bold; background-color: #f9fafb; width: 30%; }
        .footer { text-align: center; margin-top: 5px; font-size: 12px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 15px; position: absolute; bottom: 0; width: 100%; }
        .stamp { text-align: right; margin-top: 40px; font-weight: bold; color: #3b82f6; }
    </style>
</head>
<body>

    <div class="header">
        <div class="app-title">HORIZON APP</div>
        <div class="doc-title">REÇU D'INSCRIPTION OFFICIEL</div>
    </div>

    <p>Le gérant de Horizon certifie avoir enregistré l'inscription suivante :</p>

    <table class="details-table">
        <tr>
            <td class="label">Numéro de Reçu</td>
            <td style="font-weight: bold; color: #3b82f6;">{{ $enrollment->receipt_number }}</td>
        </tr>
        <tr>
            <td class="label">Nom de l'Apprenant</td>
            <td>{{ $enrollment->client->name }}</td>
        </tr>
        <tr>
            <td class="label">Téléphone / Email</td>
            <td>{{ $enrollment->client->phone }} / {{ $enrollment->client->email ?? 'Non renseigné' }}</td>
        </tr>
        <tr>
            <td class="label">Formation Intégrée</td>
            <td style="font-weight: bold;">{{ $enrollment->course->title }}</td>
        </tr>
        <tr>
            <td class="label">Niveau & Formateur</td>
            <td>{{ $enrollment->course->level }} — Par {{ $enrollment->course->trainer_name }}</td>
        </tr>
        <tr>
            <td class="label">Statut du Paiement</td>
            <td><strong>{{ $enrollment->payment_status }}</strong></td>
        </tr>
        <tr>
            <td class="label">Montant Versé</td>
            <td style="font-weight: bold; color: #10b981;">{{ number_format($enrollment->amount_paid, 0, ',', ' ') }} FCFA</td>
        </tr>
        <tr>
            <td class="label">Date d'inscription</td>
            <td>{{ $enrollment->created_at->format('d/m/Y à H:i') }}</td>
        </tr>
    </table>

    <div class="stamp">
        <p>Fait à Yaoundé, le {{ date('d/m/Y') }}</p>
        <p style="margin-top: 10px; font-size: 14px; text-decoration: underline;">La Direction Horizon</p>
    </div>

    <div class="footer">
        Horizon App — Système de gestion intégré. Document généré automatiquement et valide sans signature.
    </div>

</body>
</html>