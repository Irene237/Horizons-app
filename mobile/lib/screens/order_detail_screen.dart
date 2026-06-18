import 'package:flutter/material.dart';
import 'package:flutter/foundation.dart' show kIsWeb; // Important pour la détection Web
import 'package:url_launcher/url_launcher.dart';
import '../models/order.dart';

class OrderDetailScreen extends StatelessWidget {
  final Order order;

  const OrderDetailScreen({super.key, required this.order});

  // Fonction corrigée pour gérer l'URL selon l'environnement
  Future<void> _launchFile(String path) async {
    // Choix de l'IP selon la plateforme
    final String host = kIsWeb ? '127.0.0.1' : '10.0.2.2';
    final Uri url = Uri.parse('http://$host:8000/storage/$path');
    
    if (await canLaunchUrl(url)) {
      await launchUrl(url, mode: LaunchMode.externalApplication);
    } else {
      debugPrint('Impossible d\'ouvrir : $url');
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text("Détail #${order.id}")),
      body: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text("Client: ${order.customerName}", style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
            const Divider(),
            ListTile(title: const Text("Statut"), trailing: Text(order.status.toUpperCase())),
            ListTile(title: const Text("Total"), trailing: Text("${order.totalAmount} FCFA")),
            const Spacer(),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                // On vérifie si le chemin n'est pas vide ou null
                onPressed: (order.filePath != null && order.filePath!.isNotEmpty)
                    ? () => _launchFile(order.filePath!)
                    : null,
                child: const Text("VOIR LE FICHIER / MAQUETTE"),
              ),
            ),
            const SizedBox(height: 20),
          ],
        ),
      ),
    );
  }
}