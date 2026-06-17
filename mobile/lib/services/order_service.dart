import 'dart:convert';
import 'dart:io' show Platform; // Import nécessaire pour Platform
import 'package:flutter/foundation.dart'; // Import nécessaire pour kIsWeb et debugPrint
import 'package:http/http.dart' as http;
import '../models/order.dart';

class OrderService {
  // Détection automatique de l'environnement
  String get baseUrl {
    if (kIsWeb) {
      return "http://127.0.0.1:8000/api";
    } else if (Platform.isAndroid) {
      return "http://10.0.2.2:8000/api";
    }
    return "http://127.0.0.1:8000/api";
  }

  Future<List<Order>> getOrders(String token) async {
    final url = Uri.parse('$baseUrl/print-orders');
    
    try {
      final response = await http.get(
        url,
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
        },
      );

      if (response.statusCode == 200) {
        List body = jsonDecode(response.body);
        return body.map((item) => Order.fromJson(item)).toList();
      } else {
        // Utilisation correcte de debugPrint
        debugPrint('Erreur API (${response.statusCode}): ${response.body}');
        throw Exception('Erreur ${response.statusCode}: Impossible de charger les commandes');
      }
    } catch (e) {
      debugPrint('Exception attrapée: $e');
      throw Exception('Erreur de connexion : $e');
    }
  }
}